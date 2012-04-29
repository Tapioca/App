define([
  'config',
  'underscore'
], function(config)
{

  var globals = {
  };
  _.extend(globals, config);
  return globals;

});
