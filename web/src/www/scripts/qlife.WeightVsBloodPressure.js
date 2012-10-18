/**
 * WeightVsBloodPressure CrossDevice
 * @copyright 2012, Mikhail Yurasov
 */

qlife.WeightVsBloodPressure = function (options) {
  qlife.CrossDevice.apply(this, arguments);

  var self = this;

  function init() {
    self.matchData();
    self.transformData();
    self.display();
  }


  /**
   * Convert data for charts
   */
  self.transformData = function () {
    var data;

    // [{time, body: {weight}, blood: {pulse, systolic, diastolic}, ...]

    data = {
      wvsbpMean: [],
      wvsbpSys: [],
      wvsbpDia: []
    };

    _.each(self.data, function (measure) {
      // [[weight, bpMean], ...]
      data.wvsbpMean.push([
        measure.body.weight,
        (measure.blood.systolic + measure.blood.diastolic) / 2,
      ]);

      // [[weight, systolic], ...]
      data.wvsbpSys.push([
        measure.body.weight,
        measure.blood.systolic,
      ]);

      // [[weight, diastolic], ...]
      data.wvsbpDia.push([
        measure.body.weight,
        measure.blood.diastolic,
      ]);
    });

    self.transformedData = data;
  }

  self.display = function () {
    new Highcharts.Chart({
      chart: {
        renderTo: self.createChartElement(),
        type: 'scatter',
        zoomType: "xy"
      },
      title: {
        text: null
      },
      xAxis: {
        title: {
          enabled: true,
          text: 'Weight (lbs)'
        },
        type: "linear"
      },
      yAxis: {
        title: {
          text: 'Blood pressure (mmHg)'
        }
      },
      tooltip: {
        enabled: false,
        formatter: function() {
          return '' + Math.round(this.x * 10) / 10 + ' lbs<br>'+ Math.round(this.y * 10) / 10 +' mmHg';
        }
      },
      legend: {
        layout: 'vertical',
        align: 'left',
        verticalAlign: 'top',
        x: 100,
        y: 70,
        floating: true,
        backgroundColor: '#FFFFFF',
        borderWidth: 1
      },
      plotOptions: {
        scatter: {
          marker: {
            radius: 5,
            states: {
              hover: {
                enabled: true,
                lineColor: "hsla(0,0%,0%,0)"
              }
            }
          },
          states: {
            hover: {
              marker: {
                enabled: false
              }
            }
          }
        },
        series: {
          marker: {
            enabled: true
          }
        }
      },
      series: [{
        color: "hsla(258,100%,65%,0.6)",
        marker: {
          symbol: "circle"
        },
        data: self.transformedData.wvsbpMean
      }, {
        color: "hsla(25,100%,65%, 0.5)",
        marker: {
          symbol: "circle"
        },
        data: self.transformedData.wvsbpSys
      }, {
        color: "hsla(45,100%,55%,0.5)",
        marker: {
          symbol: "circle"
        },
        data: self.transformedData.wvsbpDia
      }]
    });

  }

  init();
}