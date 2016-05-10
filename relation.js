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

        var layers = [];

        var layersStr = input.val();
        if(layersStr)
          layers = layersStr.split(',');

        layers.forEach(function(layer) {
          appendLayer(relatedList, layer);
        });

        relatedList.on('click', '.remove', function() {
          var layerId = $(this).parent().data('layerid');
          $(this).parent().remove();
          layers.remove(layers.indexOf(layerId));
          updateInput(input, layers);
        });

        container.on('click', '.layer-item > a', function(e) {
          e.preventDefault();
          // ADD LAYER
          var layerId = $(this).parent().data('layerid');
          if(layers.indexOf(layerId) == -1) {
            appendLayer(relatedList, layerId);
            layers.push(layerId);
            updateInput(input, layers);
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

  var appending = [];

  function appendLayer(container, layerId) {
    if(appending.indexOf(layerId) == -1) {
      appending.push(layerId);
      domegis.getLayer(layerId, function(layer) {
        appending.remove(appending.indexOf(layer.id));
        $layer = $('<li data-layerid="' + layer.id + '" />');
        $layer.html('[<a href="#" class="remove">x</a>] ' + layer.name);
        container.append($layer);
        domegis.getViews({
          layerId: layer.id
        }, function(res) {
          var views = res.data;
          if(views.length) {
            var $views = $('<ul />');
            $layer.append($views);
            views.forEach(function(view) {
              var $li = $('<li data-viewid="' + view.id + '" />');
              var $input = $('<input />');
              var ref ='domegis-view-' + view.id;
              $input.attr('id', ref);
              $input.attr('type', 'radio');
              $input.attr('name', 'domegis_layer_view[' + layer.id + ']');
              $input.attr('value', view.id);
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

  function updateInput(input, layers) {
    input.val(layers.join(','));
  }

})(jQuery);
