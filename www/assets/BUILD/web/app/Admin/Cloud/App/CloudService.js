// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
define([

], function(

) {
  class CloudServiceOn {
    isCloud() { return true; }
  }

  class CloudServiceOff {
    isCloud() { return false; }
  }

  if (window.DP_IS_CLOUD) {
    return CloudServiceOn;
  } else {
    return CloudServiceOff;
  }
});