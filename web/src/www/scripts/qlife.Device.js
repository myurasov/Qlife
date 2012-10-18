/**
 * Device
 * @copyright 2012, Mikhail Yurasov
 */

qlife.Device = function (template, data, options) {
  var self = this;
  var element, $element;
  var deleteAlert;

  self.data = null;
  self.element = null;
  self.$element = null;
  self.transformedData = [];

  self.colors = {
    area: "hsla(258,65%,65%,0.6)",
    line1: "hsla(25,95%,65%,1)",
    line2: "hsla(45,100%,65%,1)",
    line3: "hsla(5,100%,72%,1)",
    line4: "hsla(200,100%,45%,1)",
    line5: "hsla(190,100%,65%,1)",
    line6: "hsla(330,80%,75%,1)",
    line7: "hsla(55,100%,65%,1)",
    line8: "hsla(258,50%,65%,1)",
    line9: "hsla(150,90%,50%,1)",
    line10: "hsla(258,50%,65%,1)",
  }

  /**
   * Construct
   */
  function init() {
    self.data = data;
    self.options = _.defaults(options || {}, {});
    createElement();

    if (data.recentData) {
      data.recentData = self.sortRecentData(data.recentData);
    }

    self.configureHighchart();
    attachEvents();
  }

  function attachEvents() {
    $(".delete", element).click(deleteDevice);
    $(".private input", element).change(function () {
      var isPrivate = $(this).attr("checked") ? "1" : "0";
      $.ajax({
        url: "/API/Device/update",
        data: {
          nocache: Math.random(),
          id: self.data.id,
          isPrivate: isPrivate
        }
      })
    })
  }

  function deleteDevice() {
    if (!deleteAlert) {
      deleteAlert = new mym.Alert(".deleteAlertTemplate");
      deleteAlert.addEventListener("buttonClick", function (e) {
        if (e.index == 1) {
          self.$element.animate({
            opacity: 0,
            height: 0
          }, 400, function () {
            self.$element.detach();
          });
        }
      });
    }

    deleteAlert.show();
  }

  self.configureHighchart = function () {
    Highcharts.setOptions({
      chart: {
        style: {
          fontFamily: $(document.body).css("font-family"),
          fontSize: "15px",
        },
        animation: true,
        backgroundColor: "rgba(0,0,0,0)",
        zoomType: "x"
      },
      title: {
        style: {
          color: "hsla(0,0%,0%,0.44)",
          fontSize: "16px",
          fontStyle: "italic"
        }
      },
      legend: {
        enabled: false
      },
      xAxis: {
        type: "datetime",
        tickPosition: "inside",
        labels: {
          style: {
            fontSize: "12px",
            fontWeight: "bold",
            color: "hsla(0,0%,0%,0.33)"
          }
        },
        lineColor: "hsla(0,0%,80%,1)",
        title: {
          style: {
            color: "hsla(0,0%,0%,0.33)",
            fontWeight: "normal",
            fontSize: "14px",
          }
        }
      },
      yAxis: {
        labels: {
          style: {
            fontSize: "12px",
            fontWeight: "bold",
            color: "hsla(0,0%,0%,0.33)"
          }
        },
        title: {
          style: {
            color: "hsla(0,0%,0%,0.33)",
            fontWeight: "normal",
            fontSize: "14px",
          }
        }
      },
      tooltip: {
        crosshairs: true,
        shared: true,
        useHTML: true,
        style: {
          fontSize: "13px"
        },
        formatter: function() {
          var s = '<span style="color:hsla(0,0%,0%,0.75);font-size:11px;line-height:1.5">'
            + self.formatTimestamp(this.x) +'</span>';

          $.each(this.points, function(i, point) {
            var vs = point.series.tooltipOptions.valueSuffix || "";
            s += '<br/><i>'+ point.series.name + '</i>: ' +  point.y + vs;
          });

          return s;
        }
      },
      plotOptions: {
        series: {
          marker: {
            enabled: false
          }
        }
      },
      credits : {
        enabled : false
      }
    });
  }

  self.sortRecentData = function(data) {
    // sort data by timestamp
    return _.sortBy(data, function (e) { return e.time; });
  }

  /**
   * Create DOM element
   */
  function createElement() {
    self.element = utils.elementFromTemplate(
      template,
      data
    );

    $(".content .devices").append(self.element);
    self.$element = $(self.element);

    element = self.element;
    $element = self.$element;
  }

  self.formatTimestamp = function (ts) {
    return (new Date(ts)).format('ddd, mmm dS yyyy, h:MM:ss TT');
  }

  self.createChartElement = function () {
    var $chart = $('<div class="chart"></div>');
    $(".charts", self.$element).append($chart);
    return $chart[0];
  }

  // abstract functions
  self.transformData = function() {}
  self.display = function() {}

  //

  init();
}

/**
 * Device factory
 */
qlife.Device.createDevice = function (element, data, options) {

  if (data.deviceClass == "bpm") {
    return new qlife.BloodPressureMonitor(element, data, options);
  } else if (data.deviceClass == "andws") {
    return new qlife.ANDWeightScale(element, data, options);
  } else if (data.deviceClass == "entra") {
    return new qlife.EntraGlucometer(element, data, options);
  } else if (data.deviceClass == "nonin") {
    return new qlife.NoninPulseOximeter(element, data, options);
  }
}