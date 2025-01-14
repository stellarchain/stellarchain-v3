#!/bin/bash

# File to keep track of the last processed ledger range
STATE_FILE="/var/log/ledger_reingest_state"

# Default start and end ledgers if the state file doesn't exist
START_LEDGER=1
END_LEDGER=30000
WORKERS=4
TOTAL_HISTORY_LEDGER=50000000  # Adjust this value to the total number of historical ledgers

# PHP script to run after reingestion
PHP_SCRIPT="/path/to/your_metrics_script.php"

# Check if the state file exists
if [ -f "$STATE_FILE" ]; then
  # Read the last processed range
  source "$STATE_FILE"
else
  # Initialize the state file with default values
  echo "START_LEDGER=$START_LEDGER" > "$STATE_FILE"
  echo "END_LEDGER=$END_LEDGER" >> "$STATE_FILE"
fi

while true; do
  echo "Starting reingestion for range $START_LEDGER to $END_LEDGER..."

  # Run the reingestion command
  stellar-horizon db reingest range "$START_LEDGER" "$END_LEDGER" --parallel-workers "$WORKERS"
  if [ $? -ne 0 ]; then
    echo "Reingestion failed for range $START_LEDGER to $END_LEDGER. Exiting."
    exit 1
  fi

  echo "Reingestion completed for range $START_LEDGER to $END_LEDGER."

  # Run the PHP script to generate metrics
  echo "Running PHP metrics script..."
  php "$PHP_SCRIPT"
  if [ $? -ne 0 ]; then
    echo "PHP script execution failed. Exiting."
    exit 1
  fi
  echo "Metrics generation completed."

  # Update the range for the next run
  START_LEDGER=$((END_LEDGER + 1))
  END_LEDGER=$((END_LEDGER + 30000))

  # Check if we've completed the full history
  if [ "$START_LEDGER" -gt "$TOTAL_HISTORY_LEDGER" ]; then
    echo "All historical ledgers have been processed. Exiting."
    break
  fi

  # Save the updated range to the state file
  echo "START_LEDGER=$START_LEDGER" > "$STATE_FILE"
  echo "END_LEDGER=$END_LEDGER" >> "$STATE_FILE"

  echo "Next range to process: $START_LEDGER to $END_LEDGER."
done

echo "Process completed."
