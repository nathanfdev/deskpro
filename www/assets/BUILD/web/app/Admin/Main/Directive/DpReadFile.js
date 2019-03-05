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
  const Admin_Main_Directive_DpReadFile = [
    '$parse', $parse =>
      ({
        restrict: 'A',
        scope:    false,
        link(scope, element, attrs) {
          const fn = $parse(attrs.dpReadFile);

          return element.on('change', (onChangeEvent) => {
            const reader = new FileReader();

            reader.onload = onLoadEvent =>
              scope.$apply(() => fn(scope, { $fileContent: (onLoadEvent.target != null ? onLoadEvent.target.result : undefined) || onLoadEvent.result }))
            ;


            const fileSrc = onChangeEvent.srcElement || onChangeEvent.target;
            return reader.readAsText(fileSrc.files[0]);
          });
        }
      })

  ];

  return Admin_Main_Directive_DpReadFile;
});
