define [

], (

) ->
  class CloudServiceOn
    isCloud: -> return true

  class CloudServiceOff
    isCloud: -> return false

  if window.DP_IS_CLOUD
    return CloudServiceOn
  else
    return CloudServiceOff