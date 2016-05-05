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
  console.log(query);
  jQuery.get(this.url + '/layers', query, function(res) {
    if(typeof cb == 'function') {
      cb(res);
    }
  }, 'json');
};
