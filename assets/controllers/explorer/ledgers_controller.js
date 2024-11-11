import {Controller} from '@hotwired/stimulus';
import {timeAgo} from 'app';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ['jobsList'];

  async initialize() {
    const ledgerData = {
      "hash": "548393ec23959e1959a62f003029ecf96be89e13df036073bf64918996ec4227",
      "prev_hash": "446d6eca81dd6db6daf50d93ca9d297bd60b1233b91de3765cccdf503cfffcb0",
      "sequence": 26857634,
      "successful_transaction_count": 27,
      "failed_transaction_count": 1,
      "operation_count": 133,
      "tx_set_operation_count": 134,
      "closed_at": "2024-11-11 15:55",
      "total_coins": 105443902087.3472865,
      "fee_pool": 1807038.9789761,
      "base_fee_in_stroops": 100,
      "base_reserve_in_stroops": 5000000,
      "protocol_version": 12
    };

    document.getElementById("sequence").textContent = `#${ledgerData.sequence}`;
    document.getElementById("total_coins").textContent = `${ledgerData.total_coins.toLocaleString()}`;
    document.getElementById("fee_pool").textContent = `$${ledgerData.fee_pool.toLocaleString()}`;
    document.getElementById("base_fee_in_stroops").textContent = `${ledgerData.base_fee_in_stroops}`;
    document.getElementById("successful_transaction_count").textContent = ledgerData.successful_transaction_count;
    document.getElementById("failed_transaction_count").textContent = ledgerData.failed_transaction_count;
    document.getElementById("operation_count").textContent = ledgerData.operation_count;
    document.getElementById("closed_at").textContent = timeAgo(ledgerData.closed_at);
  }
}
