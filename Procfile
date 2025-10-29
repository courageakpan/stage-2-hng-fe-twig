web: sh -c '
  echo "Starting PHP server..."
  echo "Environment PORT is: ${PORT:-not set}"
  if [ -z "$PORT" ]; then
    PORT=8000
    echo "PORT not set — defaulting to $PORT"
  fi
  php -S 0.0.0.0:$PORT -t .'
