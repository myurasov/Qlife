/**
 * BloodPressureMonitor Device
 * @copyright 2012, Mikhail Yurasov
 */

qlife.BloodPressureMonitor = function (element, data, options) {
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

    // convert
    // [{time, blood: {pulse, systolic, diastolic}}, ...]

    data = {
      range: [],
      pulse: [],
      avgPulse: [],
      avgSys: [],
      avgDia: []
    };

    var avgSumDia = 0;
    var avgSumSys = 0;
    var avgSumPulse = 0;
    var daysTotal = (_.last(self.data.recentData).time - self.data.recentData[0].time) / (60 * 60 * 24);
    var avgPeriod = Math.max(Math.round(self.data.recentData.length / daysTotal), 7);

    for (var i = 0; i < self.data.recentData.length; i++) {

      // range
      // [[timeMs, diastolic, systolic], ...]

      data.range.push([
        self.data.recentData[i].time * 1000,
        self.data.recentData[i].blood.diastolic,
        self.data.recentData[i].blood.systolic,
      ]);

      // pulse
      // [[timeMs, pulse], ...]

      data.pulse.push([
        self.data.recentData[i].time * 1000,
        self.data.recentData[i].blood.pulse,
      ]);

      // avg pulse
      // [[timeMs, avg], ...]

      avgSumPulse +=  self.data.recentData[i].blood.pulse;

      if (i >= avgPeriod) {
        avgSumPulse -= self.data.recentData[i - avgPeriod].blood.pulse;

        data.avgPulse.push([
          self.data.recentData[i].time * 1000,
          Math.round(avgSumPulse / avgPeriod)
        ]);
      }


      // avg dia
      // [[timeMs, avg], ...]

      avgSumDia +=  self.data.recentData[i].blood.diastolic;

      if (i >= avgPeriod) {
        avgSumDia -= self.data.recentData[i - avgPeriod].blood.diastolic;

        data.avgDia.push([
          self.data.recentData[i].time * 1000,
          Math.round(avgSumDia / avgPeriod)
        ]);
      }

      // avg sys
      // [[timeMs, avg], ...]

      avgSumSys +=  self.data.recentData[i].blood.systolic;

      if (i >= avgPeriod) {
        avgSumSys -= self.data.recentData[i - avgPeriod].blood.systolic;

        data.avgSys.push([
          self.data.recentData[i].time * 1000,
          Math.round(avgSumSys / avgPeriod)
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
        type: "arearange"
      },
      title: {
        text: "Blood Pressure"
      },
      yAxis: {
        title: {
          text: "mmHg"
        }
      },
      tooltip: {
        valueSuffix: " mmHg",
        formatter: function() {
          var s = '<span style="color:hsla(0,0%,0%,0.75);font-size:11px;line-height:1.5">'
            + self.formatTimestamp(this.x) +'</span>';

          $.each(this.points, function(i, point) {
            var vs = point.series.tooltipOptions.valueSuffix || "";
            if (i == 0) {
              s += '<br/><i>'+ point.series.name + '</i>: '
                +  point.point.high + '/' + point.point.low + ' mmHg (&Delta; = ' +
                (point.point.high - point.point.low) + ')';
            } else {
              s += '<br/><i>'+ point.series.name + '</i>: ' +  point.y + vs;
            }
          });

          return s;
        }
      },
      series: [{
        name: "Blood Pressure",
        data: self.transformedData.range,
        color: self.colors.area
      }, {
        name: "Systolic Trend",
        data: self.transformedData.avgSys,
        type: "line",
        color: self.colors.line1
      }, {
        name: "Diastolic Trend",
        data: self.transformedData.avgDia,
        type: "line",
        color: self.colors.line2
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