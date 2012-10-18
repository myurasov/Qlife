/**
 * Profile
 * @copyright 2012, Mikhail Yurasov
 */

qlife.Profile = function (options) {
  var self = this;
  var element;
  var $element;

  /**
   * Construct
   */
  function init() {
    self.options = _.defaults(options || {}, {
      element: ".content"
    });

    $element = $(options.element);
    element = $element[0];

    if (qlife.app.pageData.devices) {
      createCrossDevices();
      createDevices();
      sortDevices();
    }

    attachEvents();
  }

  function attachEvents() {
  }

  function sortDevices() {
    // sort data
    /*_.sortBy(qlife.app.pageData.devices, function (d) {
      return d.sortIndex;
    });*/

    // sort nodes
    var d = $(".devices");
    var c = d.children();
    c.detach();
    c = _.sortBy(c, function (e) { return parseInt($(e).attr("_sortIndex")); });
    d.append(c);
  }

  function createDevices() {
    // create devices

    qlife.app.devices = [];
    _.each(qlife.app.pageData.devices, function (device, i) {
      qlife.app.devices.push(
        qlife.Device.createDevice(".deviceTemplate", device)
      );
    });
  }

  function createCrossDevices() {
    // weight vs bp

    var devices = [];

    _.each(qlife.app.pageData.devices, function (device) {
      if (device.serialNumber == "2NET00004" || device.serialNumber == "2NET00003") {
        devices.push(device);
      }
    });

    qlife.app.weightVsBloodPressure = new qlife.WeightVsBloodPressure({
      template: ".crossDeviceTemplate",
      name: "Weight vs Blood Pressure Analysis",
      type: "wvsbp",
      sortIndex: 15,
      devices: devices
    });
  }

  init();
}