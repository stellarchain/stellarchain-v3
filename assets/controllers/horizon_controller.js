import {Controller} from '@hotwired/stimulus';
import StellarSdk from '@stellar/stellar-sdk';
const {Horizon, Asset} = StellarSdk;
import {createChart} from 'lightweight-charts';


/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ['payments', 'trades', 'chart'];

  async initialize() {

    const assetCode = this.element.dataset.horizonAssetCodeValue;
    const assetIssuer = this.element.dataset.horizonAssetIssuerValue;
    const asset = new Asset(assetCode, assetIssuer);
    const server = new Horizon.Server("https://horizon.stellar.org");
    this.listenPayments(server, asset);
    this.listenOrderbook(server, asset);
    this.listenTrades(server, asset);
    this.loadAggregatedTradesChart(server, asset);
    this.getAsset(server, assetCode, assetIssuer)
  }

  loadAggregatedTradesChart(server, asset) {
    const chartOptions = {
      layout: {textColor: 'white', background: {type: 'solid', color: 'transparent'}},
      grid: {
        vertLines: {color: '#2b2b2b'},
        horzLines: {color: '#2b2b2b'},
      }
    };
    const chart = createChart(document.getElementById('trades-chart'), chartOptions);
    const volumeSeries = chart.addHistogramSeries({
      priceFormat: {
        type: 'volume',
      },
      priceScaleId: '', // set as an overlay by setting a blank priceScaleId
    });
    volumeSeries.priceScale().applyOptions({
      scaleMargins: {
        top: 0.9, // highest point of the series will be 70% away from the top
        bottom: 0,
      },
    });
    const candlestickSeries = chart.addCandlestickSeries({
      upColor: 'rgb(246, 70, 93)', downColor: 'rgb(46, 189, 133)', borderVisible: false,
      wickUpColor: 'rgb(246, 70, 93)', wickDownColor: 'rgb(46, 189, 133)',
    });

    const toolTip = document.createElement('div');
    toolTip.style = `width: 96px; height: 80px; position: absolute; display: none; padding: 8px; box-sizing: border-box; font-size: 12px; text-align: left; z-index: 1000; top: 12px; left: 12px; pointer-events: none; border: 1px solid; border-radius: 2px;font-family: -apple-system, BlinkMacSystemFont, 'Trebuchet MS', Roboto, Ubuntu, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;`;
    toolTip.style.background = 'white';
    toolTip.style.color = 'black';
    toolTip.style.borderColor = '#2962FF';

    this.chartTarget.appendChild(toolTip);
    chart.subscribeCrosshairMove(param => {
      if (
        param.point === undefined ||
        !param.time ||
        param.point.x < 0 ||
        param.point.y < 0
      ) {
        toolTip.style.display = 'none';
      } else {
        toolTip.style.display = 'block';
        const data = param.seriesData.get(candlestickSeries);
        const price = data.value !== undefined ? data.value : data.close;
        const close = data.close !== undefined ? data.close : 'no close';
        toolTip.innerHTML = `<div>${1 / price.toFixed(2)} ${close}</div>`;

        toolTip.style.left = param.point.x + 'px';
        toolTip.style.top = param.point.y + 'px';
      }
    });

    const es = server.tradeAggregation(asset, Asset.native(), 1582178400000, Date.now(), 86400000, 0).limit(100).call().then(
      (message) => {
        const transformedData = message.records.map(record => ({
          time: new Date(parseInt(record.timestamp)).toISOString().split('T').join(' ').slice(0, 16), // Convert timestamp to 'YYYY-MM-DD HH:mm'
          open: parseFloat(record.open),
          high: parseFloat(record.high),
          low: parseFloat(record.low),
          close: parseFloat(record.close)
        }));

        const volumeData = message.records.map((item) => {
          return {
            time: new Date(parseInt(item.timestamp)).toISOString().split('T').join(' ').slice(0, 16),
            value: item.base_volume,
            color:
              item.open > item.close
                ? "rgba(239, 83, 80, 0.5)"
                : "rgba(38, 166, 154, 0.5)",
          };
        });
        volumeSeries.setData(volumeData);
        candlestickSeries.setData(transformedData);
        chart.timeScale().fitContent();
      }
    );
  }

  listenPayments(server, asset) {
  }

  getAsset(server, assetCode, assetIssuer) {
    server.assets().forCode(assetCode).forIssuer(assetIssuer).call().then(res => {
      res = res.records[0]
      document.getElementById('total_amount').textContent = Number(res.amount).toLocaleString();
      document.getElementById('claimable_balances').textContent = Number(res.claimable_balances_amount).toLocaleString();
      document.getElementById('liquidity_pools').textContent = Number(res.liquidity_pools_amount).toLocaleString();
      document.getElementById('contracts_amount').textContent = res.contracts_amount;
      document.getElementById('contractId').textContent = res.contract_id ? res.contract_id : 'No contract';

      document.getElementById('authorized_accounts').textContent = res.accounts.authorized;
      document.getElementById('unauthorized_accounts').textContent = res.accounts.unauthorized;
      document.getElementById('authorized_liabilities').textContent = res.accounts.authorized_to_maintain_liabilities;

      document.getElementById('balances_authorized').textContent = res.balances.authorized;
      document.getElementById('balances_unauthorized').textContent = res.balances.unauthorized;
      document.getElementById('balances_liabilities').textContent = res.balances.authorized_to_maintain_liabilities;

      document.getElementById('archived_contracts_amount').textContent = res.archived_contracts_amount;
      document.getElementById('num_archived_contracts').textContent = res.num_archived_contracts;
      document.getElementById('num_claimable_balances').textContent = res.num_claimable_balances;
      document.getElementById('num_contracts').textContent = res.num_contracts;

    })
  }

  listenOrderbook(server, asset) {
    const es = server.orderbook(asset, Asset.native())
      .cursor('now')
      .stream({
        onmessage: this.handleOrderBook.bind(this)
      })
  }

  listenTrades(server, asset) {
    const es = server.trades().forAssetPair(asset, Asset.native())
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
    <td>${this.timeAgo(ledgerCloseTime)}</td>
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
      bidRow.classList.add('bid-item', 'small', 'font-monospace');

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
      askRow.classList.add('ask-item', 'small', 'font-monospace');

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
  handlePayments(message) {
    console.log(message)
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
