(function() {
  DomeGIS = function(url) {
    this.url = url;
    return this;
  };

  DomeGIS.prototype.getContents = function(query, cb) {
    jQuery.get(this.url + '/contents', query, function(res) {
      if(typeof cb == 'function') {
        cb(res);
      }
    }, 'json');
  };

  DomeGIS.prototype.getLayers = function(query, cb) {
    jQuery.get(this.url + '/layers', query, function(res) {
      if(typeof cb == 'function') {
        cb(res);
      }
    }, 'json');
  };

  DomeGIS.prototype.getViews = function(query, cb) {
    jQuery.get(this.url + '/views', query, function(res) {
      if(typeof cb == 'function') {
        cb(res);
      }
    }, 'json');
  };

  DomeGIS.prototype.getLayer = function(id, cb) {
    jQuery.get(this.url + '/layers/' + id, function(res) {
      if(typeof cb == 'function') {
        cb(res);
      }
    }, function(err) {
      console.log(err);
    }, 'json')
  };

  DomeGIS.prototype.search = function(term, cb) {
    jQuery.get(this.url + '/search', {
      term: term
    }, function(res) {
      if(typeof cb == 'function') {
        cb(res);
      }
    });
  };

  DomeGIS.prototype.featureSearch = function(layerId, term, cb) {
    jQuery.get(this.url + '/layers/' + layerId + '/search', {
      term: term
    }, function(res) {
      if(typeof cb == 'function') {
        cb(res);
      }
    });
  }
})();
