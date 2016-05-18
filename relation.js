// Array Remove - By John Resig (MIT Licensed)
Array.prototype.remove = function(from, to) {
  var rest = this.slice((to || from) + 1 || this.length);
  this.length = from < 0 ? this.length + from : from;
  return this.push.apply(this, rest);
};

(function($) {

  var domegis = new DomeGIS(domegis_relation.settings.url);

  $(document).ready(function() {
    if($('#domegis-meta-box').length) {
      var $box = $('#domegis-meta-box');
      $box.each(function() {
        var box = $(this);

        var container = $('<div class="domegis-contents" />');
        box.find('.inside .search-results').append(container);

        var input = box.find('#domegis_related_layers_input');
        var relatedList = $('<ul />');
        box.find('.related-results').append(relatedList);

        var layers = JSON.parse(input.val());

        if(_.isArray(layers)) {
          layers.forEach(function(layerId) {
            appendLayer(relatedList, layerId, false);
          });
        } else {
          for(var layerId in layers) {
            appendLayer(relatedList, layerId, layers[layerId]);
          }
        }

        relatedList.on('click', '.remove', function() {
          var layerId = $(this).parent().data('layerid');
          $(this).parent().remove();
          delete layers[layerId];
        });

        container.on('click', '.layer-item > a', function(e) {
          e.preventDefault();
          // ADD LAYER
          var layerId = $(this).parent().data('layerid');
          if(!layers[layerId]) {
            appendLayer(relatedList, layerId);
            layers[layerId] = true;
          }
        });

        /*
         * Search
         */
        box.find('.domegis-search').on('keydown', function() {
          container.empty().append('<p>Searching...</p>');
        });
        box.find('.domegis-search').on('keydown', _.debounce(function() {
          var val = box.find('.domegis-search').val();
          if(val) {
            getList(val, function(list) {
              container.empty().append(list);
            });
          } else {
            container.empty();
          }
        }, 200));

        /*
         * Feature search
         */
        var fContainer = $('<div class="domegis-features" />');
        box.find('.domegis-related-feature .feature-results').append(fContainer);
        var fSelectedContainer = box.find('.selected-feature');

        fContainer.on('click', '.feature-item > a', function(e) {
          e.preventDefault();
          // SELECT FEATURE
          var feature = {
            id: $(this).parent().data('featureid'),
            layerId: $(this).parent().data('layerid'),
            label: $(this).parent().data('featurelabel')
          };
          selectFeature(fSelectedContainer, feature);
        });

        box.find('.domegis-feature-search').on('keydown', function() {
          fContainer.empty().append('<p>Searching...</p>');
        });
        box.find('.domegis-feature-search').on('keydown', _.debounce(function() {
          var val = box.find('.domegis-feature-search').val();
          if(val) {
            getFeatList(Object.keys(layers), val, function(list) {
              fContainer.empty().append(list);
            });
          } else {
            fContainer.empty();
          }
        }, 300));
      });
    }
  });

  function getList(searchTerm, cb) {
    var $list = $('<ul />');
    domegis.search(searchTerm, function(res) {
      var layers = res.layers;
      if(!layers.length) {
        if(typeof cb == 'function') {
          cb($('<p>No results were found.</p>'));
        }
      } else {
        layers.forEach(function(layer) {
          var $item = $('<li id="layer-' + layer.id + '" class="layer-item" />');
          var $a = $('<a href="#" class="toggle" />').text(layer.name);
          $item.attr('data-layerid', layer.id);
          $item.append($a);
          $list.append($item);
          if(typeof cb == 'function') {
            cb($list);
          }
        });
      }
    });
  }

  function getFeatList(layerIds, searchTerm, cb) {
    var $list = $('<ul />');
    var count = 0;
    var total = layerIds.length;
    var _cb = function(res) {
      count++;
      var features = res.features;
      if(features.length) {
        features.forEach(function(feature) {
          var propVal = foundPropVal(searchTerm, feature);
          var $item = $('<li id="feature-' + feature.layerId + '-' + feature.id + '" class="feature-item" />');
          var $a = $('<a href="#" class="toggle" />').text(propVal);
          $item.attr('data-featureid', feature.id);
          $item.attr('data-layerid', feature.layerId);
          $item.attr('data-featurelabel', propVal);
          $item.append($a);
          $list.append($item);
          if(count == total) {
            _return($list);
          }
        });
      }
    };
    var _return = function() {
      if(typeof cb == 'function') {
        if($list.find('li').length) {
          cb($list);
        } else {
          cb('<p>No results were found.</p>');
        }
      }
    }
    layerIds.forEach(function(id) {
      domegis.featureSearch(id, searchTerm, _cb);
    });
  }

  function foundPropVal(term, object) {
    return _.find(object, function(item, key) {
      if(typeof item == 'string')
        return item.indexOf(term) !== -1;
    });
  }

  var appending = [];

  function appendLayer(container, layerId, viewId) {
    if(appending.indexOf(layerId) == -1) {
      appending.push(layerId);
      var $layer = $('<li data-layerid="' + layerId + '" />');
      domegis.getLayer(layerId, function(layer) {
        appending.remove(appending.indexOf(layer.id));
        $layer.html(layer.name + ' <a href="#" class="button remove" tabindex="0">x</a> ');
        container.append($layer);
        domegis.getViews({
          layerId: layer.id
        }, function(res) {
          var views = res.data;
          if(views.length) {
            var $views = $('<ul />');
            $layer.append($views);
            views.forEach(function(view, i) {
              var selected = false;
              if((viewId && viewId == view.id) || i == 0) {
                selected = true;
              }
              var $li = $('<li data-viewid="' + view.id + '" />');
              var $input = $('<input />');
              var ref ='domegis-view-' + view.id;
              $input.attr('id', ref);
              $input.attr('type', 'radio');
              $input.attr('name', 'domegis_layer_view[' + layer.id + ']');
              $input.attr('value', view.id);
              if(selected)
                $input.attr('checked', true);
              var $label = $('<label />');
              $label.attr('for', ref);
              $label.text(view.name);
              $li.append($input).append($label);
              $views.append($li);
            });
          }
        });
      });
    }
  }

  function selectFeature(container, feature) {
    container.find('h5').text(feature.label);
    container.find('#domegis_related_feature_id').val(feature.id);
    container.find('#domegis_related_feature_layerid').val(feature.layerId);
    container.find('#domegis_related_feature_label').val(feature.label);
  }

})(jQuery);
