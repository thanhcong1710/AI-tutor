#!/bin/bash

# AI Tutor - Laravel Helper Script
# Chạy các lệnh Laravel trong Docker container php82

CONTAINER="php82"
PROJECT_PATH="/var/www/html/ai_tutor"

case "$1" in
    "migrate")
        echo "🔄 Running migrations..."
        docker exec $CONTAINER php $PROJECT_PATH/artisan migrate
        ;;
    
    "migrate:fresh")
        echo "⚠️  Dropping all tables and re-running migrations..."
        docker exec $CONTAINER php $PROJECT_PATH/artisan migrate:fresh --seed --force
        ;;
    
    "seed")
        echo "🌱 Seeding database..."
        docker exec $CONTAINER php $PROJECT_PATH/artisan db:seed
        ;;
    
    "serve")
        echo "🚀 Starting Laravel development server..."
        echo "📍 Access at: http://localhost:8000"
        docker exec -it $CONTAINER php $PROJECT_PATH/artisan serve --host=0.0.0.0 --port=8000
        ;;
    
    "tinker")
        echo "🔧 Opening Tinker..."
        docker exec -it $CONTAINER php $PROJECT_PATH/artisan tinker
        ;;
    
    "cache:clear")
        echo "🧹 Clearing cache..."
        docker exec $CONTAINER php $PROJECT_PATH/artisan cache:clear
        docker exec $CONTAINER php $PROJECT_PATH/artisan config:clear
        docker exec $CONTAINER php $PROJECT_PATH/artisan route:clear
        docker exec $CONTAINER php $PROJECT_PATH/artisan view:clear
        ;;
    
    "queue:work")
        echo "⚙️  Starting queue worker..."
        docker exec -it $CONTAINER php $PROJECT_PATH/artisan queue:work
        ;;
    
    "test")
        echo "🧪 Running tests..."
        docker exec $CONTAINER php $PROJECT_PATH/artisan test
        ;;
    
    "logs")
        echo "📋 Tailing Laravel logs..."
        docker exec -it $CONTAINER tail -f $PROJECT_PATH/storage/logs/laravel.log
        ;;
    
    "composer")
        shift
        echo "📦 Running composer $@..."
        docker exec $CONTAINER composer --working-dir=$PROJECT_PATH $@
        ;;
    
    "artisan")
        shift
        echo "🎨 Running artisan $@..."
        docker exec $CONTAINER php $PROJECT_PATH/artisan $@
        ;;
    
    "bash")
        echo "💻 Opening bash in container..."
        docker exec -it $CONTAINER bash
        ;;
    
    *)
        echo "🤖 AI Tutor - Laravel Helper"
        echo ""
        echo "Usage: ./ai-tutor.sh [command]"
        echo ""
        echo "Available commands:"
        echo "  migrate          - Run database migrations"
        echo "  migrate:fresh    - Drop all tables and re-run migrations with seed"
        echo "  seed             - Seed the database"
        echo "  serve            - Start Laravel development server"
        echo "  tinker           - Open Laravel Tinker"
        echo "  cache:clear      - Clear all caches"
        echo "  queue:work       - Start queue worker"
        echo "  test             - Run tests"
        echo "  logs             - Tail Laravel logs"
        echo "  composer [args]  - Run composer commands"
        echo "  artisan [args]   - Run artisan commands"
        echo "  bash             - Open bash in container"
        echo ""
        echo "Examples:"
        echo "  ./ai-tutor.sh serve"
        echo "  ./ai-tutor.sh migrate"
        echo "  ./ai-tutor.sh artisan route:list"
        echo "  ./ai-tutor.sh composer require package/name"
        ;;
esac
