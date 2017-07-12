<?php

namespace MakinaCorpus\Drupal\Dashboard\Page;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use MakinaCorpus\Drupal\Dashboard\Form\Type\SelectionFormType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SymfonyFormPageBuilder extends PageBuilder
{
    use StringTranslationTrait;

    /**
     * @var \Symfony\Component\Form\Form
     */
    private $confirmForm;

    /**
     * @var mixed
     */
    private $storedData;

    /**
     * @var bool
     */
    private $requestHandled = false;

    /**
     * @var bool
     */
    private $confirmationCancelled = false;

    /**
     * @var \Symfony\Component\Form\Form
     */
    private $formset;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    private $formFactory;

    public function __construct(
        \Twig_Environment $twig,
        EventDispatcherInterface $dispatcher,
        FormFactory $formFactory
    ) {
        $this->formFactory = $formFactory;
        parent::__construct($twig, $dispatcher);
    }

    /**
     * Builds and enables the confirm form
     *
     * @return $this
     */
    public function enableConfirmForm()
    {
        $formBuilder = $this->formFactory
            ->createNamedBuilder('confirm')
            ->add('confirm', SubmitType::class, ['label' => $this->t('Confirm')])
            ->add('cancel', SubmitType::class, ['label' => $this->t('Cancel')])
        ;

        $this->confirmForm = $formBuilder->getForm();

        return $this;
    }

    /**
     * If the page is to be inserted as a form widget, set the element name
     *
     * Please notice that in all cases, only the template can materialize the
     * form element, this API is agnostic from any kind of form API and cannot
     * do it automatically.
     *
     * This parameter will only be carried along to the twig template under
     * the 'form_name' variable. It is YOUR job to create the associated
     * inputs in the final template.
     *
     * @param string $class
     *   Form parameter name.
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createFormset($class = SelectionFormType::class)
    {
        if (!is_subclass_of($class, AbstractType::class)) {
            throw new \InvalidArgumentException(sprintf("Class %s doesn't extend %s.", $class, AbstractType::class));
        }

        $this->formset = $this->formFactory
            ->createNamedBuilder('formset')
            ->add('forms', CollectionType::class, [
                'entry_type' => $class,
                'label'      => false,
                // TODO: find a way to make configurable (we dont always want a selection)
                // implement validation groups with submits?
                // @see http://symfony.com/doc/current/form/button_based_validation.html
                //'constraints' => [
                //    new Callback([$this, 'hasSelectedValue']),
                //],
            ])
            ->getForm()
        ;

        return $this->formset;
    }

    /**
     * Returns true if this form page needs confirmation.
     *
     * @return bool
     */
    public function needsConfirmation()
    {
        if (!$this->requestHandled) {
            throw new LogicException("The request has not been handled by this PageFormBuilder yet.");
        }

        // A form needs confirmation if it has confirmForm (not cancelled)...
        if ($this->confirmForm && !$this->confirmationCancelled) {
            // If the form has been submitted and is valid
            return $this->formset->isSubmitted() && $this->formset->isValid();
        }

        return false;
    }

    /**
     * Check that form has selected values
     *
     * @param $value
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function hasSelectedValue($value, ExecutionContextInterface $context)
    {
        $selectedElements = array_filter($value, function ($d) {
            return !empty($d['selected']);
        });

        if (!$selectedElements) {
            $context
                ->buildViolation($this->t("No items selected."))
                ->addViolation()
            ;
        }
    }

    /**
     * Indicate if the formset is ready to process
     *
     * @return bool
     */
    public function isReadyToProcess()
    {
        if (!$this->requestHandled) {
            throw new LogicException("The request has not been handled by this PageFormBuilder yet.");
        }

        // In order to be ready, form must be confirmed and has data
        if ($this->confirmForm && (!$this->confirmForm->isSubmitted() || $this->confirmationCancelled)) {
            return false;
        }

        return (bool)$this->getStoredData();
    }

    /**
     * Get data
     *
     * @param string $name
     * @return mixed
     */
    public function getStoredData($name = null)
    {
        if ($name) {
            if (!isset($this->storedData[$name])) {
                throw new \InvalidArgumentException(sprintf("No data named '%s'.", $name));
            }

            return $this->storedData[$name];
        }

        return $this->storedData;
    }

    /**
     * Get the loaded items from selection
     *
     * @return array[]
     */
    public function getSelectedItems()
    {
        try {
            $data = $this->getStoredData('forms');
        } catch (\InvalidArgumentException $e) {
            return [];
        }

        return array_filter(
            $data,
            function ($d) {
                return !empty($d['selected']);
            }
        );
    }

    /**
     * Get the loaded items from selection
     *
     * @return string[]
     */
    public function getSelectedIds()
    {
        return array_keys($this->getSelectedItems());
    }

    /**
     * Get the confirm form if existent
     *
     * @return \Symfony\Component\Form\Form
     */
    public function getConfirmForm()
    {
        return $this->confirmForm;
    }

    /**
     * Clear the session data
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function clearData(Request $request)
    {
        $request->getSession()->remove($this->computeId());
    }

    /**
     * {@inheritDoc}
     *
     * Also include the formset for display
     */
    public function createPageView(PageResult $result, array $arguments = [])
    {
        $arguments['formset'] = $this->formset->createView();

        return parent::createPageView($result, $arguments);
    }

    /**
     * Make the formset and confirm form handle request
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param callable $callback
     */
    public function handleRequest(Request $request, $callback = null)
    {
        // Get items to work on
        $dataItems = $this->search($request)->getItems();

        // Bind initial data
        if ($callback) {
            if (!is_callable($callback)) {
                throw new \InvalidArgumentException("The provided callback is not valid.");
            }
            $defaultValues = call_user_func($callback, $dataItems);
        }
        else {
            $defaultValues = [];
            foreach ($dataItems as $item) {
                $defaultValues[$item->id()] = [
                    'id' => $item->id(),
                ];
            }
        }

        $this->formset->setData(['forms' => $defaultValues])->handleRequest($request);

        if ($this->confirmForm) {
            $this->confirmForm->handleRequest($request);

            if (
                $this->confirmForm->isSubmitted() &&
                $this->confirmForm->getClickedButton()->getName() == 'cancel'
            ) {
                // Confirm form has been cancelled, set the data back and display form
                $this->confirmationCancelled = true;
                $data = $request->getSession()->get($this->computeId());
                $this->formset->setData($data);

            } else if ($this->formset->isSubmitted() && $this->formset->isValid()) {
                // Form has been submitted, store data into session and display confirm form.
                $data = $this->formset->getData();

                // Form could have been posted by a custom button in a template, case in which
                // it will not be available within the form; moreoever, POST'ing data without
                // using a button is valid too.
                $clickedButton = $this->formset->getClickedButton();
                if ($clickedButton) {
                    $data['clicked_button'] = $clickedButton->getName();
                } else {
                    $data['clicked_button'] = null; // Avoid errors on data fetch
                }

                $request->getSession()->set($this->computeId(), $data);
                $this->storedData = $data;

            } else {
                // Confirm form is valid, do not attempt to fetch data from original form.
                $this->storedData = $request->getSession()->get($this->computeId());
            }
        }
        else {
            // Else if no confirm form there's no need to use the session
            $this->storedData = $this->formset->getData();
        }

        $this->requestHandled = true;
    }
}
