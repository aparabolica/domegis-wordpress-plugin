(function($) {

  var domegis = new DomeGIS(domegis_settings.url);

  $(document).ready(function() {
    if($('#domegis-meta-box').length) {
      var $box = $('#domegis-meta-box');
      $box.each(function() {
        var box = $(this);
        var container = $('<div class="domegis-contents" />');
        box.find('.inside').append(container);
        container.on('click', '.content-item > a', function(e) {
          e.preventDefault();
          toggleContentLayers($(this).parent());
        });
        getList({}, function(list) {
          container.empty().append(list);
        });
        box.find('.domegis-search').on('keydown', function() {
          container.empty().append('<p>Searching...</p>');
        });
        box.find('.domegis-search').on('keydown', _.debounce(function() {
          var val = box.find('.domegis-search').val();
          if(val) {
            getList({
              name: val
            }, function(list) {
              container.empty().append(list);
            });
          } else {
            getList({}, function(list) {
              container.empty().append(list);
            });
          }
        }, 200));
      });
    }
  });

  function getList(query, cb) {
    var $list = $('<ul />');
    domegis.getContents(query, function(res) {
      contents = res.data;
      if(!contents.length) {
        if(typeof cb == 'function') {
          cb($('<p>No results were found.</p>'));
        }
      } else {
        contents.forEach(function(content) {
          var $item = $('<li id="content-' + content.id + '" class="content-item" />');
          var $a = $('<a href="#" class="toggle" />').text(content.name);
          $item.attr('data-contentid', content.id);
          $item.append($a);
          $list.append($item);
          if(typeof cb == 'function') {
            cb($list);
          }
        });
      }
    });
  }

  function toggleContentLayers($item) {
    if($item.find('.content-layers').length) {
      $item.find('.content-layers').remove();
    } else {
      var id = $item.data('contentid');
      var $list = $('<ul class="content-layers" />');
      domegis.getLayers({
        contentId: id
      }, function(res) {
        var layers = res.data;
        $item.append($list);
        layers.forEach(function(layer) {
          var $item = $('<li />');
          $item.text(layer.name);
          $list.append($item);
        });
      });
    }
  }

})(jQuery);
