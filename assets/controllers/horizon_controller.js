import {Controller} from '@hotwired/stimulus';
import StellarSdk from '@stellar/stellar-sdk';
const {Horizon, Asset} = StellarSdk;
import {createChart, CrosshairMode, LineStyle} from 'lightweight-charts';


/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ['payments', 'trades', 'chart'];

  aggregatedTrades = null;
  chart = null;
  candlestickSeries = null;
  volumeSeries = null;
  tooltip = null;
  asset = null;
  server = null;
  loadingTrades = false;
  resolution = 86400000;
  orderBookStream = null;
  tradesStream = null;
  areaSeries = null;
  maSeries = null;
  chartContainer = null;

  async initialize() {
    this.asset = new Asset(this.element.dataset.horizonAssetCodeValue, this.element.dataset.horizonAssetIssuerValue);
    this.server = new Horizon.Server("https://horizon.stellar.org");
    this.initChart();
    this.listenOrderbook();
    this.listenTrades();
    this.loadAggregatedTradesChart();
    this.loadInitialTrades();
    this.getAsset(this.element.dataset.horizonAssetCodeValue, this.element.dataset.horizonAssetIssuerValue)
  }

  calculateMovingAverageSeriesData(candleData, maLength) {
    const maData = [];

    for (let i = 0; i < candleData.length; i++) {
      if (i < maLength) {
        // Provide whitespace data points until the MA can be calculated
        maData.push({time: candleData[i].time});
      } else {
        // Calculate the moving average, slow but simple way
        let sum = 0;
        for (let j = 0; j < maLength; j++) {
          sum += candleData[i - j].close;
        }
        const maValue = sum / maLength;
        maData.push({time: candleData[i].time, value: maValue});
      }
    }

    return maData;
  }

  disconnect() {
    if (this.orderBookStream) {
      this.orderBookStream(); // Closing orderbook stream
      this.orderBookStream = null; // Clear reference after closing
    }

    if (this.tradesStream) {
      this.tradesStream(); // Closing trades stream
      this.tradesStream = null; // Clear reference after closing
    }

    this.server = null;
    this.asset = null;
  }

  initChart() {
    this.chartContainer = document.getElementById('trades-chart');
    this.chart = createChart(this.chartContainer, {
      layout: {
        background: {color: "#222"},
        textColor: "#C3BCDB",
      },
      grid: {
        vertLines: {color: "#444"},
        horzLines: {color: "#444"},
      },
      crosshair: {
        // Change mode from default 'magnet' to 'normal'.
        // Allows the crosshair to move freely without snapping to datapoints
        mode: CrosshairMode.Normal,

        // Vertical crosshair line (showing Date in Label)
        vertLine: {
          width: 8,
          color: '#C3BCDB44',
          style: LineStyle.Solid,
          labelBackgroundColor: '#9B7DFF',
        },

        // Horizontal crosshair line (showing Price in Label)
        horzLine: {
          color: '#9B7DFF',
          labelBackgroundColor: '#9B7DFF',
        },
      }
    });
    this.volumeSeries = this.chart.addHistogramSeries({
      priceScaleId: "",
      lineWidth: 2,
      priceFormat: {
        type: "volume",
      },
      overlay: true,
      scaleMargins: {
        top: 0.9,
        bottom: 0,
      },
    });
    this.volumeSeries.priceScale().applyOptions({
      scaleMargins: {
        top: 0.9,
        bottom: 0,
      },
    });

    this.maSeries = this.chart.addLineSeries({color: '#2962FF', lineWidth: 1});

    this.areaSeries = this.chart.addAreaSeries({
      lastValueVisible: false, // hide the last value marker for this series
      crosshairMarkerVisible: false, // hide the crosshair marker for this series
      lineColor: 'transparent', // hide the line
      topColor: 'rgba(239, 83, 80, 0.5)',
      bottomColor: 'rgba(56, 33, 110, 0.1)',
    });
    this.candlestickSeries = this.chart.addCandlestickSeries({
      upColor: 'rgb(246, 70, 93)', downColor: 'rgb(46, 189, 133)', borderVisible: false,
      wickUpColor: 'rgb(246, 70, 93)', wickDownColor: 'rgb(46, 189, 133)',
    });

    this.toolTip = document.createElement('div');
    this.toolTip.style = `width: 160px; height: 80px; position: absolute; display: none; padding: 8px; box-sizing: border-box; font-size: 12px; text-align: left; z-index: 1000; top: 12px; left: 12px; pointer-events: none; border: 1px solid; border-radius: 2px;font-family: Roboto, Ubuntu, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;`;
    this.toolTip.style.background = 'black';
    this.toolTip.style.color = 'white';
    this.toolTip.style.borderColor = 'transparent';

    this.chartContainer.appendChild(this.toolTip);

    this.chart.subscribeCrosshairMove(param => {
      if (
        param.point === undefined ||
        !param.time ||
        param.point.x < 0 ||
        param.point.y < 0
      ) {
        this.toolTip.style.display = 'none';
      } else {
        let date = new Date(param.time * 1000);
        const dateStr = date.toLocaleDateString(); // This gets the date in a localized format
        const timeStr = date.toLocaleTimeString(); // This gets the time in a localized format
        const dateTimeStr = `${dateStr} ${timeStr}`;
        this.toolTip.style.display = 'block';
        const data = param.seriesData.get(this.candlestickSeries);
        const price = data.value !== undefined ? data.value : data.close;
        this.toolTip.innerHTML = `<div style="color: ${'rgba( 38, 166, 154, 1)'}"></div><div style="font-size: 24px; margin: 4px 0px; color: ${'white'}">
            ${Math.round(100 * price) / 100}
            </div><div style="color: ${'white'}">
            ${dateTimeStr}
            </div>`;
        const toolTipWidth = 120;
        const toolTipHeight = 80;
        const toolTipMargin = 15;
        const y = param.point.y;
        let left = param.point.x + toolTipMargin;
        if (left > this.chartContainer.clientWidth - toolTipWidth) {
          left = param.point.x - toolTipMargin - toolTipWidth;
        }

        let top = y + toolTipMargin;
        if (top > this.chartContainer.clientHeight - toolTipHeight) {
          top = y - toolTipHeight - toolTipMargin;
        }
        this.toolTip.style.left = left + 'px';
        this.toolTip.style.top = top + 'px';
      }
    });

    this.chart.priceScale("").applyOptions({
      scaleMargins: {
        top: 0.9,
        bottom: 0,
      },
    });

    this.chart.timeScale().subscribeVisibleLogicalRangeChange((logicalRange) => {
      if (logicalRange.from < 0 && this.candlestickSeries.data()) {
        let startTime = this.candlestickSeries.data()[0].time
        if (!this.loadingTrades) {
          this.loadingTrades = true;
          document.getElementById('loading-chart').classList.toggle('d-none');
          this.server.tradeAggregation(this.asset, Asset.native(), 0, startTime * 1000, this.resolution, 0)
            .order('desc').limit(200)
            .call().then((message) => this.addChartData(message))
        }
      }
    });
    window.addEventListener("resize", this.resizeHandler);
  }

  loadAggregatedTradesChart() {
    if (!this.loadingTrades) {
      this.loadingTrades = true;
      document.getElementById('loading-chart').classList.toggle('d-none');
      this.server.tradeAggregation(this.asset, Asset.native(), 0, 0, this.resolution, 0).order('desc').limit(200).cursor('now').call().then(
        (message) => this.addChartData(message)
      );
    }
  }

  addChartData(message) {
    const transformedData = message.records.map(record => ({
      time: parseInt(record.timestamp / 1000), // Convert timestamp to 'YYYY-MM-DD HH:mm'
      open: parseFloat(record.open),
      high: parseFloat(record.high),
      low: parseFloat(record.low),
      close: parseFloat(record.close)
    }));

    transformedData.sort((a, b) => {
      return a.time - b.time;
    });
    const volumeData = message.records.map((item) => {
      return {
        time: parseInt(item.timestamp / 1000),
        value: parseFloat(item.base_volume),
        color:
          item.open > item.close
            ? "rgba(239, 83, 80, 0.5)"
            : "rgba(38, 166, 154, 0.5)",
      };
    });

    volumeData.sort((a, b) => {
      return a.time - b.time;
    });

    // Retrieve the existing data
    const currentCandleData = this.candlestickSeries.data() || [];
    const currentVolumeData = this.volumeSeries.data() || [];

    // Prepend the new data to the existing data
    const updatedCandleData = [...transformedData, ...currentCandleData];
    const updatedVolumeData = [...volumeData, ...currentVolumeData];

    // Update the chart with the combined data
    this.candlestickSeries.setData(updatedCandleData);
    this.volumeSeries.setData(updatedVolumeData);
    const lineData = this.candlestickSeries.data().map(datapoint => ({
      time: datapoint.time,
      value: (datapoint.close + datapoint.open) / 2,
    }));

    this.areaSeries.setData(lineData)

    const maData = this.calculateMovingAverageSeriesData(this.candlestickSeries.data(), 20);
    this.maSeries.setData(maData);

    this.loadingTrades = false;
    document.getElementById('loading-chart').classList.toggle('d-none');
  }

  resizeHandler() {
    if (!this.chart) return;
    const dimensions = this.chartContainer.getBoundingClientRect();
    this.chart.resize(dimensions.width, dimensions.height);
    this.chart.timeScale().fitContent();
  }

  getAsset(assetCode, assetIssuer) {
    this.server.assets().forCode(assetCode).forIssuer(assetIssuer).call().then(res => {
      res = res.records[0]
      document.getElementById('total_amount').textContent = Number(res.amount).toLocaleString();
      document.getElementById('claimable_balances').textContent = Number(res.claimable_balances_amount).toLocaleString();
      document.getElementById('liquidity_pools').textContent = Number(res.liquidity_pools_amount).toLocaleString();
      document.getElementById('contracts_amount').textContent = res.contracts_amount;
      document.getElementById('contractId').textContent = res.contract_id ? res.contract_id : 'No contract';
      document.getElementById('authorized_accounts').textContent = res.accounts.authorized;
      document.getElementById('num_contracts').textContent = res.num_contracts;

    })
  }

  listenOrderbook() {
    this.orderBookStream = this.server.orderbook(this.asset, Asset.native())
      .cursor('now')
      .stream({
        onmessage: this.handleOrderBook.bind(this)
      })
  }

  loadInitialTrades() {
    this.server.trades().forAssetPair(this.asset, Asset.native())
      .cursor('now')
      .order('desc')
      .limit(50)
      .call().then(res => {
        res.records.map(record => this.handleTrade(record))
      })
  }

  listenTrades() {
    this.tradesStream = this.server.trades().forAssetPair(this.asset, Asset.native())
      .cursor('now')
      .stream({
        onmessage: this.handleTrade.bind(this)
      })
  }

  handleTrade(message) {
    const trades = document.querySelector('#trades tbody');

    const tradeElement = document.createElement('tr');
    tradeElement.classList.add('trade-item');
    tradeElement.classList.add('small');

    // Extracting necessary fields from the message object
    const baseAmount = message.base_amount;
    const baseAssetCode = message.base_asset_code || 'N/A';
    const counterAmount = message.counter_amount;
    const counterAssetType = message.counter_asset_type;
    const price = (message.price.n / message.price.d).toFixed(7); // Calculating price
    const ledgerCloseTime = new Date(message.ledger_close_time).toLocaleString();
    const tradeType = message.trade_type;

    tradeElement.innerHTML = `
    <td>${baseAmount}</td>
    <td>${counterAmount}</td>
    <td>${price}</td>
    <td class="text-muted">${this.timeAgo(ledgerCloseTime)}</td>
  `;

    // Append the trade row (<tr>) to the <tbody> of trades table
    trades.appendChild(tradeElement);
  }

  handleOrderBook(message) {
    const bids = message.bids;
    const asks = message.asks;

    const bidTableBody = document.querySelector('#bidTable tbody');
    const askTableBody = document.querySelector('#askTable tbody');

    // Clear the existing rows before populating new data
    bidTableBody.innerHTML = '';
    askTableBody.innerHTML = '';

    // Find the largest amount in bids and asks to normalize the percentage
    const maxBidAmount = Math.max(...bids.map(bid => parseFloat(bid.amount)));
    const maxAskAmount = Math.max(...asks.map(ask => parseFloat(ask.amount)));

    // Populate the bid table with linear-gradient backgrounds
    bids.forEach(bid => {
      const bidRow = document.createElement('tr');
      bidRow.classList.add('bid-item', 'small');

      const amount = parseFloat(bid.amount);
      const price = bid.price;

      // Calculate percentage for background width based on the amount
      const percentage = (amount / maxBidAmount) * 100;

      // Apply linear-gradient as background
      bidRow.style.background = `linear-gradient(to left, rgba(22, 163, 74, 0.1) ${percentage}%, transparent ${percentage}%)`;

      bidRow.innerHTML = `
      <td class="text-start">${amount}</td>
      <td class="text-end text-success">${price}</td>
    `;

      bidTableBody.appendChild(bidRow);
    });

    // Populate the ask table with linear-gradient backgrounds
    asks.forEach(ask => {
      const askRow = document.createElement('tr');
      askRow.classList.add('ask-item', 'small');

      const amount = parseFloat(ask.amount);
      const price = ask.price;

      // Calculate percentage for background width based on the amount
      const percentage = (amount / maxAskAmount) * 100;

      // Apply linear-gradient as background
      askRow.style.background = `linear-gradient(to right, rgba(220, 38, 38, 0.1) ${percentage}%, transparent ${percentage}%)`;

      askRow.innerHTML = `
      <td class="text-start text-danger">${price}</td>
      <td class="text-end">${amount}</td>
    `;

      askTableBody.appendChild(askRow);
    });
  }

  timeAgo(ledgerCloseTime) {
    const now = new Date();
    const closeTime = new Date(ledgerCloseTime);
    const diffInSeconds = Math.floor((now - closeTime) / 1000);

    let interval = Math.floor(diffInSeconds / 31536000);
    if (interval >= 1) return interval + (interval === 1 ? " year ago" : " years ago");

    interval = Math.floor(diffInSeconds / 2592000);
    if (interval >= 1) return interval + (interval === 1 ? " month ago" : " months ago");

    interval = Math.floor(diffInSeconds / 604800);
    if (interval >= 1) return interval + (interval === 1 ? " week ago" : " weeks ago");

    interval = Math.floor(diffInSeconds / 86400);
    if (interval >= 1) return interval + (interval === 1 ? " day ago" : " days ago");

    interval = Math.floor(diffInSeconds / 3600);
    if (interval >= 1) return interval + (interval === 1 ? " hour ago" : " hours ago");

    interval = Math.floor(diffInSeconds / 60);
    if (interval >= 1) return interval + (interval === 1 ? " minute ago" : " minutes ago");

    if (diffInSeconds >= 1) return diffInSeconds + (diffInSeconds === 1 ? " second ago" : " seconds ago");

    return "just now";
  }
}
