(function() {
  var langConfig = {
    'en': 'en',
    'pt': 'pt',
    'es': 'es'
  };

  String.prototype.langsplit = function(_regEx){
    if ('a~b'.split(/(~)/).length === 3){ return this.split(_regEx); }

    if (!_regEx.global) {
      _regEx = new RegExp(_regEx.source, 'g' + (_regEx.ignoreCase ? 'i' : ''));
    }

    var start = 0, arr=[];
    var result;
    while((result = _regEx.exec(this)) != null){
      arr.push(this.slice(start, result.index));
      if(result.length > 1) arr.push(result[1]);
      start = _regEx.lastIndex;
    }
    if(start < this.length) arr.push(this.slice(start));
    if(start == this.length) arr.push('');
    return arr;
  };

  langGetSplitBlocks = function(text) {
    var split_regex = /(<!--:[a-z]{2}-->|<!--:-->|\[:[a-z]{2}\]|\[:\]|\{:[a-z]{2}\}|\{:\})/gi;
    return text.langsplit(split_regex);
  };

  langSplit = function(text) {
    var blocks = langGetSplitBlocks(text);
    return langSplitBlocks(blocks);
  }

  langSplitBlocks = function(blocks) {
    var result = new Object;
    for(var lang in langConfig) {
      result[lang] = '';
    }
    if(!blocks || !blocks.length)
      return result;
    if(blocks.length == 1) {
      var b = blocks[0];
      for(var lang in langConfig) {
        result[lang] += b;
      }
      return result;
    }
    var clang_regex = /<!--:([a-z]{2})-->/gi;
    var blang_regex = /\[:([a-z]{2})\]/gi;
    var slang_regex = /\{:([a-z]{2})\}/gi;
    var lang = false;
    var matches;
    for(var i = 0; i < blocks.length; ++i) {
      var b = blocks[i];
      if(!b.length) continue;
      matches = clang_regex.exec(b);
      clang_regex.lastIndex = 0;
      if(matches != null) {
        lang = matches[1];
        continue;
      }
      matches = blang_regex.exec(b);
      blang_regex.lastIndex = 0;
      if(matches != null) {
        lang = matches[1];
        continue;
      }
      matches = slang_regex.exec(b);
      slang_regex.lastIndex = 0;
      if(matches != null) {
        lang = matches[1];
        continue;
      }
      if(b == '<!--:-->' || b == '[:]' || b == '{:}') {
        lang = false;
        continue;
      }
      if(lang) {
        if(!result[lang]) result[lang] = b;
        else result[lang] += b;
        lang = false;
      } else { //keep neutral text
        for(var key in result) {
          result[key] += b;
        }
      }
    }
    return result;
  }

  function langJoin(obj) {
    var str = '';
    for(var key in obj) {
      str += '[:' + key + ']' + obj[key];
    }
    return str;
  }

})();
