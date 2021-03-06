<?php

namespace MakinaCorpus\Drupal\Dashboard\Action;

interface ActionProviderInterface
{
    /**
     * Get actions for item
     *
     * @param mixed $item
     *   Object type is at the discretion of the implentor.
     * @param bool $primaryOnly = false
     *   If set to true, only primary actions are asked for, implementors can
     *   ignore this, it will be filtered by the upper layer, but you should
     *   implement this correctly for performance reasons.
     * @param string[] $groups
     *   If set, only the given groups will be asked for, if you do not
     *   implement any shortcut, they will be removed on display anyway;
     *   if this is an empty array, all groups are accounted for
     *
     * @return Action[]
     */
    public function getActions($item, $primaryOnly = false, array $groups = []);

    /**
     * Does this provider supports the given item
     *
     * @todo we need a type instead for performances
     *
     * @param mixed $item
     *
     * @return boolean
     */
    public function supports($item);
}
