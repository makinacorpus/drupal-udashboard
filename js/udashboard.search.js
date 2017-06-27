(function ($, Drupal) {
  "use strict";

  Drupal.behaviors.udashboardSearch = {
    attach: function (context, settings) {
      $('.udashboard-search-form', context).once('udashboard-search', function () {
        var $form = $(this);
        var $container = $form.find('.udashboard-visual-search');
        var query = $container.data('query');
        var definition = $container.data('definition');
        // query must be an object
        query = !$.isArray(query) ? query : {};

        var search = VS.init({
          container: $container,
          remainder: false,
          autosearch: false,
          callbacks: {
            search: function (query, searchCollection) {
              $form.submit();
            },
            facetMatches: function (callback) {
              callback(definition);
            },
            valueMatches: function (facet, searchTerm, callback) {
              // First find the corresponding setting
              var filter = _.findWhere(definition, {value: facet});

              // Process options
              if (typeof filter.options === "object") {
                var options = _.map(filter.options, function (label, value) {
                  return {label: label, value: value.toString()};
                });
                callback(options);
              }
            }
          }
        });

        // Some integration tweaks
        var width = $container.width();
        $container.css('float', 'left');
        var inputWidth = $container.next().width();
        $container.width(width - inputWidth - 1);

        // Initialize query from filters from our query string
        var facets = [];
        _.each(query, function (val, category) {
          if (_.findWhere(definition, {value: category})) {
            facets.push(new VS.model.SearchFacet({
              category: category,
              value: VS.utils.inflector.trim(val),
              app: search
            }));
          }
        });
        search.searchQuery.reset(facets);

        // Submission handling
        $form.submit(function () {
          // First remove all filters from our query string
          _.each(query, function (val, filter) {
            if (_.findWhere(definition, {value: filter})) {
              delete query[filter];
            }
          });

          // Add search filters
          _.each(search.searchQuery.facets(), function (obj) {
            $.extend(query, obj);
          });

          var newLocation = location.pathname;
          if (!$.isEmptyObject(query)) {
            newLocation += '?' + $.param(query);
          }

          location.href = newLocation;
          return false;
        });
      });
    }
  };

})(jQuery, Drupal);
