@echo off
setlocal enabledelayedexpansion

REM ==========================
REM Sta. Rosa Vet - Local Docker Parity Runner (Windows)
REM ==========================

REM 1) Build image
echo [1/3] Building Docker image...
docker build -t sta-rosa-vet:dev .
if errorlevel 1 (
  echo Build failed.
  exit /b 1
)

REM 2) Run container (Cloud Run-like)
echo [2/3] Starting container on http://localhost:8080 ...
echo Press CTRL+C to stop.
docker run --rm -p 8080:8080 --env-file docker-dev.env sta-rosa-vet:dev

