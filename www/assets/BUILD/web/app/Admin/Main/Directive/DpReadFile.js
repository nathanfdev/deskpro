// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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
        scope: false,
        link(scope, element, attrs) {
          const fn = $parse(attrs.dpReadFile);

          return element.on('change', function(onChangeEvent) {
            const reader = new FileReader();

            reader.onload = onLoadEvent =>
              scope.$apply(() => fn(scope, {$fileContent: (onLoadEvent.target != null ? onLoadEvent.target.result : undefined) || onLoadEvent.result}))
            ;


            const fileSrc = onChangeEvent.srcElement || onChangeEvent.target;
            return reader.readAsText(fileSrc.files[0]);
          });
        }
      })
    
  ];

  return Admin_Main_Directive_DpReadFile;
});