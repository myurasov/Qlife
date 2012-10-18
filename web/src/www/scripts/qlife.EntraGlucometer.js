/**
 * EntraGlucometer Device
 * @copyright 2012, Mikhail Yurasov
 */

qlife.EntraGlucometer = function (element, data, options) {
  // inherits from Device
  qlife.Device.apply(this, arguments);

  var self = this;

  function init() {
    self.transformData();
    self.display();
  }

  /**
   * Convert data for charts
   */
  self.transformData = function () {
    var data;

    // [{time, blood: {glucose}, ...]

    data = {
      glucose: [],
      avgGlucose: []
    };

    var avgSumGlucose = 0;
    var daysTotal = (_.last(self.data.recentData).time - self.data.recentData[0].time) / (60 * 60 * 24);
    var avgPeriod = Math.max(Math.round(self.data.recentData.length / daysTotal), 7);

    for (var i = 0; i < self.data.recentData.length; i++) {

      // glucose
      // [[timeMs, glucose], ...]

      data.glucose.push([
        self.data.recentData[i].time * 1000,
        self.data.recentData[i].blood.glucose
      ]);

      // avg glucose
      // [[timeMs, avgGlucose], ...]

      avgSumGlucose +=  self.data.recentData[i].blood.glucose;

      if (i >= avgPeriod) {
        avgSumGlucose -= self.data.recentData[i - avgPeriod].blood.glucose;

        data.avgGlucose.push([
          self.data.recentData[i].time * 1000,
          Math.round(avgSumGlucose / avgPeriod * 10) / 10
        ]);
      }

    }

    self.transformedData = data;
  }

  self.display = function () {

    new Highcharts.Chart({

      chart: {
        renderTo: self.createChartElement(),
        // line|spline|area|areaspline|column|bar|pie|scatter
        // arearange|areasplinerange|columnrange
        type: "line"
      },
      title: {
        text: "Blood Glucose Level"
      },
      yAxis: {
        title: {
          text: "mg/dL"
        }
      },
      tooltip: {
        valueSuffix: " mg/dL"
      },
      series: [{
        name: "Glucose Level",
        data: self.transformedData.glucose,
        color: self.colors.line7
      }, {
        name: "Glucose Level Trend",
        data: self.transformedData.avgGlucose,
        color: self.colors.line8
      }]
    });
  }

  init();
}