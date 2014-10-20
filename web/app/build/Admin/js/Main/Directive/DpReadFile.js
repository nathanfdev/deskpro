(function() {
  define(function() {

    /*
        * Description
        * -----------
        *
        * Read an input file and pass the text contents to a function
        *
        * Example
        * -------
        * <input type="file" dp-read-file="readContentsFunction($fileContent)" />
     */
    var Admin_Main_Directive_DpReadFile;
    Admin_Main_Directive_DpReadFile = [
      '$parse', function($parse) {
        return {
          restrict: 'A',
          scope: false,
          link: function(scope, element, attrs) {
            var fn;
            fn = $parse(attrs.dpReadFile);
            return element.on('change', function(onChangeEvent) {
              var fileSrc, reader;
              reader = new FileReader();
              reader.onload = function(onLoadEvent) {
                return scope.$apply(function() {
                  var _ref;
                  return fn(scope, {
                    $fileContent: ((_ref = onLoadEvent.target) != null ? _ref.result : void 0) || onLoadEvent.result
                  });
                });
              };
              fileSrc = onChangeEvent.srcElement || onChangeEvent.target;
              return reader.readAsText(fileSrc.files[0]);
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpReadFile;
  });

}).call(this);

//# sourceMappingURL=DpReadFile.js.map
