/**
 * NoninPulseOximeter Device
 * @copyright 2012, Mikhail Yurasov
 */

qlife.NoninPulseOximeter = function (element, data, options) {
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

    // [{time, blood: {pulse, spo2}, ...]

    data = {
      pulse: [],
      spo2: [],
      avgPulse: [],
      avgSpo2: []
    };

    var avgSumPulse = 0;
    var avgSumSpo2 = 0;
    var daysTotal = (_.last(self.data.recentData).time - self.data.recentData[0].time) / (60 * 60 * 24);
    var avgPeriod = Math.max(Math.round(self.data.recentData.length / daysTotal), 7);

    for (var i = 0; i < self.data.recentData.length; i++) {

      // pulse
      // [[timeMs, pulse], ...]

      data.pulse.push([
        self.data.recentData[i].time * 1000,
        self.data.recentData[i].blood.pulse
      ]);

      // avg pulse
      // [[timeMs, avgPulse], ...]

      avgSumPulse +=  self.data.recentData[i].blood.pulse;

      if (i >= avgPeriod) {
        avgSumPulse -= self.data.recentData[i - avgPeriod].blood.pulse;

        data.avgPulse.push([
          self.data.recentData[i].time * 1000,
          Math.round(avgSumPulse / avgPeriod * 10) / 10
        ]);
      }

      // spo2
      // [[timeMs, spo2], ...]

      data.spo2.push([
        self.data.recentData[i].time * 1000,
        self.data.recentData[i].blood.spo2
      ]);

      // avg spo2
      // [[timeMs, avgPulse], ...]

      avgSumSpo2 +=  self.data.recentData[i].blood.spo2;

      if (i >= avgPeriod) {
        avgSumSpo2 -= self.data.recentData[i - avgPeriod].blood.spo2;

        data.avgSpo2.push([
          self.data.recentData[i].time * 1000,
          Math.round(avgSumSpo2 / avgPeriod * 10) / 10
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
        text: "Blood Oxygen Level"
      },
      yAxis: {
        title: {
          text: "%"
        }
      },
      tooltip: {
        valueSuffix: " %"
      },
      series: [{
        name: "Oxygen Level",
        data: self.transformedData.spo2,
        color: self.colors.line5
      }, {
        name: "Oxygen Level Trend",
        data: self.transformedData.avgSpo2,
        color: self.colors.line6
      }]

    });

    new Highcharts.Chart({

      chart: {
        renderTo: self.createChartElement(),
        // line|spline|area|areaspline|column|bar|pie|scatter
        // arearange|areasplinerange|columnrange
        type: "line"
      },
      title: {
        text: "Pulse"
      },
      yAxis: {
        title: {
          text: "bpm"
        }
      },
      tooltip: {
        valueSuffix: " bpm"
      },
      series: [{
        name: "Pulse",
        data: self.transformedData.pulse,
        color: self.colors.line3
      }, {
        name: "Pulse Trend",
        data: self.transformedData.avgPulse,
        color: self.colors.line4
      }]

    });

  }

  init();
}