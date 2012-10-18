/**
 * Utils
 *
 * @copyright 2012, Mikhail Yurasov
 */

if (typeof utils == "undefined") utils = {};

utils.trim = function(str)
{
  if (typeof str == "string") {
    return str.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
  }
  else {
    return str;
  }
};

utils.elementFromTemplate = function (template, data) {
  var element = $(template).html();
  element = _.template(element, data);
  return $(element).get(0);
}

utils.repeat = function (str, n) {
  return new Array(n + 1).join(str);
}