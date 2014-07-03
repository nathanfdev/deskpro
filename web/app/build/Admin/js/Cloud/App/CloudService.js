(function() {
  define([], function() {
    var CloudServiceOff, CloudServiceOn;
    CloudServiceOn = (function() {
      function CloudServiceOn() {}

      CloudServiceOn.prototype.isCloud = function() {
        return true;
      };

      return CloudServiceOn;

    })();
    CloudServiceOff = (function() {
      function CloudServiceOff() {}

      CloudServiceOff.prototype.isCloud = function() {
        return false;
      };

      return CloudServiceOff;

    })();
    if (window.DP_IS_CLOUD) {
      return CloudServiceOn;
    } else {
      return CloudServiceOff;
    }
  });

}).call(this);

//# sourceMappingURL=CloudService.js.map
