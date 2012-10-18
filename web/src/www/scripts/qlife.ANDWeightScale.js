/**
 * ANDWeightScale Device
 * @copyright 2012, Mikhail Yurasov
 */

qlife.ANDWeightScale = function (element, data, options) {
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

    // [{time, body: {weight}, ...]

    data = {
      weight: [],
      avgWeight: []
    };

    var avgSumWeight = 0;
    var daysTotal = (_.last(self.data.recentData).time - self.data.recentData[0].time) / (60 * 60 * 24);
    var avgPeriod = Math.max(Math.round(self.data.recentData.length / daysTotal), 7);

    for (var i = 0; i < self.data.recentData.length; i++) {

      // weight
      // [[timeMs, weight], ...]

      data.weight.push([
        self.data.recentData[i].time * 1000,
        Math.round(self.data.recentData[i].body.weight * 10) / 10
      ]);

      // avg weight
      // [[timeMs, avgWeight], ...]

      avgSumWeight +=  self.data.recentData[i].body.weight;

      if (i >= avgPeriod) {
        avgSumWeight -= self.data.recentData[i - avgPeriod].body.weight;

        data.avgWeight.push([
          self.data.recentData[i].time * 1000,
          Math.round(avgSumWeight / avgPeriod * 10) / 10
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
        text: "Body Weight"
      },
      yAxis: {
        title: {
          text: "lbs"
        }
      },
      tooltip: {
        valueSuffix: " lbs"
      },
      series: [{
        name: "Weight",
        data: self.transformedData.weight,
        color: self.colors.line9
      }, {
        name: "Weight Trend",
        data: self.transformedData.avgWeight,
        color: self.colors.line10
      }]
    });
  }

  init();
}