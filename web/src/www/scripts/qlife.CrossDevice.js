/**
 * CrossDevice Device
 * @copyright 2012, Mikhail Yurasov
 */

qlife.CrossDevice = function (options) {
  qlife.Device.call(this, options.template, options);

  var self = this;

  function init() {
    // default options
    self.options = _.defaults(options || {}, {
      name: "",
      template: "",
      devices: []
    });
  }

  function findClosestMeasure(measure, data, startIndex) {
    var dt;
    var dtPrev = -1;

    for (var i = startIndex; i < data.length; i++) {
      dt = Math.abs(measure.time - data[i].time);
      if (dtPrev != -1 && dt > dtPrev) break;
      dtPrev = dt;
    }

    return i - 1;
  }

  /**
   * Match devices data by timestamp
   * We assume that devices data is sorted by time, ASC
   */
  self.matchData = function () {

    var data = [];

    for (var i = 1; i < self.options.devices.length; i++) {

      var closestIndex = 0;

      _.each(self.options.devices[0].recentData, function (measure, ind) {

        // find closest index in current device data
        closestIndex = findClosestMeasure(
          measure,
          self.options.devices[i].recentData,
          closestIndex
        );

        // combine measures, keeping first device time
        data.push(_.defaults(
          measure,
          self.options.devices[i].recentData[closestIndex]
        ));
      });
    }

    self.data = data;
  }

  init();
}