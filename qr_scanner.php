<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner - Health Records</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <style>
        :root {
            --primary-color: #2ecc71;
            --secondary-color: #27ae60;
            --accent-color: #3498db;
            --text-color: #2c3e50;
            --light-gray: #f5f6fa;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --success-color: #27ae60;
            --error-color: #e74c3c;
            --warning-color: #f39c12;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect fill="%23000" fill-opacity="0.03" width="100" height="100"/></svg>') repeat;
            pointer-events: none;
            z-index: -1;
        }

        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 1.5rem;
            width: 100%;
            border-radius: 0 0 20px 20px;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent 0%,
                rgba(255, 255, 255, 0.1) 50%,
                transparent 100%
            );
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .main-content {
            max-width: 1200px;
            width: 100%;
            padding: 0 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 1;
        }

        .scanner-container {
            background: var(--white);
            padding: 2.3rem;
            border-radius: 15px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            width: 100%;
            max-width: 720px;
            height: 74vh;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            border: 3px solid var(--primary-color);
            backdrop-filter: blur(10px);
        }

        #preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }

        .scanner-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent-color), transparent);
            animation: scan 2s linear infinite;
            box-shadow: 0 0 15px var(--accent-color);
            z-index: 1;
        }

        .scanner-guide {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 250px;
            height: 250px;
            border: 2px solid rgba(52, 152, 219, 0.5);
            border-radius: 10px;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
            animation: pulse 2s infinite;
            z-index: 2;
        }

        .scanner-guide::before,
        .scanner-guide::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: calc(100% + 20px);
            height: calc(100% + 20px);
            border: 2px solid rgba(52, 152, 219, 0.3);
            border-radius: 15px;
            animation: guideExpand 2s infinite;
        }

        .scanner-guide::after {
            animation-delay: 1s;
        }

        @keyframes guideExpand {
            0% {
                width: 100%;
                height: 100%;
                opacity: 1;
            }
            100% {
                width: calc(100% + 40px);
                height: calc(100% + 40px);
                opacity: 0;
            }
        }

        .scan-area-label {
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(52, 152, 219, 0.9);
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.9rem;
            white-space: nowrap;
            z-index: 3;
        }

        .scanner-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(
                45deg,
                rgba(52, 152, 219, 0.1),
                rgba(46, 204, 113, 0.1)
            );
            z-index: 1;
            pointer-events: none;
        }

        .camera-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 15px;
            z-index: 3;
        }

        .camera-button {
            background: rgba(0, 0, 0, 0.6);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .camera-button:hover {
            background: rgba(0, 0, 0, 0.8);
            transform: scale(1.1);
        }

        .camera-button:active {
            transform: scale(0.95);
        }

        .camera-button i {
            font-size: 1.2rem;
        }

        .scan-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.7);
            z-index: 10;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .scan-overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .scan-message {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px 30px;
            border-radius: 10px;
            text-align: center;
            transform: translateY(20px);
            transition: all 0.3s ease;
        }

        .scan-overlay.active .scan-message {
            transform: translateY(0);
        }

        .scan-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 4px;
            background: var(--primary-color);
            width: 0%;
            transition: width 0.3s ease;
        }

        .form-container {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 400px;
            margin-bottom: 2rem;
            transition: var(--transition);
            text-align: center;
            position: relative;
            backdrop-filter: blur(10px);
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 15px;
            padding: 2px;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }

        .form-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .scanned-id {
            text-align: center;
            font-size: 1.2rem;
            color: var(--accent-color);
            margin: 1rem 0;
            font-weight: 500;
            padding: 1.5rem;
            border: 2px dashed var(--accent-color);
            border-radius: 10px;
            background: rgba(52, 152, 219, 0.1);
            transition: all 0.3s ease;
        }

        .scanned-id.success {
            border-color: var(--success-color);
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
            animation: successPulse 2s infinite;
        }

        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        .error-message {
            color: var(--error-color);
            text-align: center;
            margin-top: 1rem;
            font-weight: 500;
            padding: 1rem;
            background: rgba(231, 76, 60, 0.1);
            border-radius: 10px;
            border-left: 4px solid var(--error-color);
            transform: translateX(100%);
            opacity: 0;
            animation: slideIn 0.3s forwards;
        }

        @keyframes slideIn {
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            padding: 1rem;
            flex-wrap: wrap;
        }

        .action-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: var(--primary-color);
            color: green;
            border: 1;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            margin: 0.25rem;
            flex: 0 1 auto;
            white-space: nowrap;
        }

        .action-button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .action-button i {
            font-size: 1rem;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .loading-overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--white);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .camera-tooltip {
            position: absolute;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .scanner-container:hover .camera-tooltip {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .scanner-container {
                height: 50vh;
                padding: 1rem;
            }

            .form-container {
                padding: 1rem;
            }

            header {
                border-radius: 0;
                margin-bottom: 1rem;
            }

            .scanner-corners {
                width: 20px;
                height: 20px;
            }

            .camera-tooltip {
                display: none;
            }
        }

        .success-animation {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 5rem;
            color: var(--success-color);
            opacity: 0;
            transition: all 0.5s ease;
            z-index: 1000;
        }

        .success-animation.active {
            opacity: 1;
            animation: successZoom 1s ease-out;
        }

        @keyframes successZoom {
            0% { transform: translate(-50%, -50%) scale(0); }
            50% { transform: translate(-50%, -50%) scale(1.2); }
            100% { transform: translate(-50%, -50%) scale(1); }
        }

        .advanced-controls {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 1000;
        }

        .control-panel {
            background: rgba(0, 0, 0, 0.85);
            border-radius: 10px;
            padding: 15px;
            color: white;
            display: none;
            position: absolute;
            right: 60px;
            min-width: 200px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .control-panel.active {
            display: block;
            animation: slideIn 0.3s ease;
        }

        .control-group {
            margin-bottom: 15px;
        }

        .control-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: #fff;
            font-weight: 500;
        }

        .slider-control {
            width: 100%;
            -webkit-appearance: none;
            height: 4px;
            border-radius: 2px;
            background: rgba(255, 255, 255, 0.3);
            outline: none;
            transition: all 0.3s ease;
        }

        .slider-control::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--primary-color);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #fff;
        }

        .slider-control::-webkit-slider-thumb:hover {
            transform: scale(1.2);
            box-shadow: 0 0 10px var(--primary-color);
        }

        .scan-history {
            position: fixed;
            left: 0;
            top: auto;
            bottom: 0;
            transform: translateY(100%);
            background: rgba(0, 0, 0, 0.95);
            border-radius: 15px 15px 0 0;
            padding: 20px;
            color: white;
            width: 100%;
            max-height: 80vh;
            overflow-y: auto;
            transition: transform 0.3s ease, opacity 0.3s ease;
            z-index: 1000;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.2);
            opacity: 0;
            backdrop-filter: blur(10px);
        }

        .scan-history.active {
            transform: translateY(0);
            opacity: 1;
        }

        .scan-history .history-header {
            position: sticky;
            top: 0;
            background: rgba(0, 0, 0, 0.95);
            padding: 15px 0;
            margin: -20px -20px 20px -20px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 2;
        }

        .scan-history .history-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .scan-history .history-title i {
            color: var(--primary-color);
        }

        .history-list {
            padding-bottom: 70px;
        }

        .history-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 2px;
            overflow:auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .history-item:active {
            transform: scale(0.98);
            background: rgba(255, 255, 255, 0.15);
        }

        .history-item-content {
            flex-grow: 1;
            margin-right: 15px;
        }

        .history-item-id {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: var(--primary-color);
        }

        .history-item-time {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .history-actions {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 15px 20px;
            background: rgba(0, 0, 0, 0.95);
            display: flex;
            justify-content: space-between;
            gap: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            .scan-history {
                display: block !important;
                max-height: 50vh;
                padding: 15px;
                border-radius: 20px 20px 0 0;
            }

            .scan-history.active {
                transform: translateY(0);
            }

            .scan-history:not(.active) {
                transform: translateY(100%);
            }

            .history-header {
                margin: -15px -15px 15px -15px;
                padding: 15px;
                
            }

            .history-list {
                margin: 0;
                padding: 0;
                list-style: none;
                padding-bottom: 20px;
            }

            .history-item {
                margin-bottom: 10px;
                padding: 12px;
            }

            .history-actions {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: rgba(0, 0, 0, 0.95);
                padding: 10px 15px;
                display: flex;
                justify-content: space-between;
                gap: 10px;
                z-index: 1001;
            }

            .history-button {
                padding: 8px 15px;
                border-radius: 8px;
                font-size: 0.9rem;
            }

            .advanced-controls,
            .scan-stats {
                display: none;
            }

            .scan-mode-switch {
                top: auto;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
            }

            .scan-counter {
                top: 10px;
                padding: 8px 15px;
                font-size: 0.9rem;
                z-index: 1002;
            }
        }

        .history-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            
        }

        .history-button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 5px;
            opacity: 0.7;
            transition: opacity 0.3s ease;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .history-button:hover {
            opacity: 1;
        }

        .history-button.clear-all {
            color: #ff6b6b;
        }

        .confirm-dialog {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.95);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            color: white;
            text-align: center;
            backdrop-filter: blur(10px);
            display: none;
        }

        .confirm-dialog.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .confirm-dialog p {
            margin: 0 0 15px 0;
        }

        .confirm-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .confirm-button {
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .confirm-button.confirm {
            background: #ff6b6b;
            color: white;
        }

        .confirm-button.confirm:hover {
            background: #ff8787;
        }

        .confirm-button.cancel {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .confirm-button.cancel:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translate(-50%, -60%);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        .upload-qr {
            position: absolute;
            top: 20px;
            right: 520px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }

        .upload-qr input[type="file"] {
            display: none;
        }

        .upload-qr label {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(0, 0, 0, 0.85);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .upload-qr label:hover {
            background: rgba(0, 0, 0, 0.95);
            transform: translateY(-1px);
        }

        .upload-qr i {
            color: var(--primary-color);
        }

        .scan-counter {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.85);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 1000;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .camera-button#toggleFlash,
        .camera-button#switchCamera,
        .camera-button#toggleFlash + .help-tooltip,
        .camera-button#switchCamera + .help-tooltip {
            display: none !important;
        }

        /* Status badge styles */
        .status-badge {
            position: absolute;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
            z-index: 3;
            text-align: center;
        }

        .status-badge i {
            font-size: 0.8rem;
        }

        .status-badge.camera-active {
            color: var(--success-color);
            background: rgba(0, 0, 0, 0.8);
        }

        @media (max-width: 768px) {
            .status-badge {
                bottom: 20px;
                font-size: 0.8rem;
                padding: 4px 12px;
            }
        }

        .history-item-actions {
            display: flex;
            gap: 8px;
        }

        .action-button {
            padding: 8px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
        }

        .action-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }

        .action-button.delete {
            color: #ff6b6b;
        }

        .action-button.delete:hover {
            background: rgba(255, 107, 107, 0.2);
        }

        .history-item.deleting {
            animation: slideOut 0.3s ease forwards;
        }

        @keyframes slideOut {
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Headline Button Styles */
        .headline-button {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            width: 100%;
            justify-content: center;
        }

        .headline-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transition: 0.5s;
        }

        .headline-button:hover::before {
            left: 100%;
        }

        .headline-button:hover {
            background: linear-gradient(135deg, #27ae60, #219a52);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
        }

        .headline-button i {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .headline-button:hover i {
            transform: translateX(-5px);
        }

        .headline-button span {
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .headline-button {
                padding: 12px 25px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    <div class="success-animation">
        <i class="fas fa-check-circle"></i>
    </div>

    <header>
        <h1>
            <i class="fas fa-qrcode fa-bounce"></i>
            QR Code Scanner
        </h1>
    </header>

    <div class="main-content">
        
        <div class="scanner-container">
            <video id="preview" autoplay></video>
            <div class="scanner-overlay">
                <div class="scanner-corners corner-tl"></div>
                <div class="scanner-corners corner-tr"></div>
                <div class="scanner-corners corner-bl"></div>
                <div class="scanner-corners corner-br"></div>
            </div>
            <div class="scanner-guide">
            </div>
            <div class="status-badge camera-active">
                <i class="fas fa-circle"></i> Camera Active
            </div>
            <div class="camera-controls">
                <button class="camera-button" id="toggleFlash">
                    <i class="fas fa-bolt"></i>
                </button>
                <button class="camera-button" id="switchCamera">
                    <i class="fas fa-camera-rotate"></i>
                </button>
            </div>
            <div class="flash-effect"></div>
            <div class="scan-progress"></div>
            <div class="scan-overlay">
                <div class="scan-message">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Processing QR Code...</p>
                </div>
            </div>
          

            <div class="scan-counter" id="scanCounterBtn">
                <i class="fas fa-qrcode"></i>
                <span>Scans: <strong id="scanCount">0</strong></span>
            </div>

            <div class="upload-qr">
                <label for="qrUpload">
                    <i class="fas fa-upload"></i>
                    Upload QR
                </label>
                <input type="file" id="qrUpload" accept="image/*">
            </div>

            <div class="advanced-controls">
              
                <button class="camera-button" id="toggleHistory" title="Scan History">
                    <i class="fas fa-history"></i>
                </button>
                <div class="control-panel">
                    <div class="control-group">
                        <label>Brightness</label>
                        <input type="range" class="slider-control" id="brightness" min="0" max="200" value="100">
                    </div>
                    <div class="control-group">
                        <label>Contrast</label>
                        <input type="range" class="slider-control" id="contrast" min="0" max="200" value="100">
                    </div>
                    <div class="control-group">
                        <label>Scan Speed</label>
                        <input type="range" class="slider-control" id="scanSpeed" min="1" max="5" value="3">
                    </div>
                </div>
            </div>

            <div class="scan-history">
                <div class="history-header">
                    <h3 class="history-title">
                        <i class="fas fa-history"></i>
                        Recent Scans
                    </h3>
                    <div class="history-header-actions">
                        <button class="history-button clear-all" title="Clear All History">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <button class="history-button history-close" title="Close History">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <ul class="history-list" id="historyList"></ul>
               
            </div>

            <div class="brightness-overlay"></div>

            <div class="scan-stats">
                <div class="stat-item">
                    <i class="fas fa-clock"></i>
                    <span id="realTime" class="real-time"></span>
                </div>
            </div>

            <div class="scan-result-preview">
                <img id="previewImage" alt="Scan Preview">
                <div id="previewInfo"></div>
            </div>
        </div>

        <div class="form-container">
            <div class="status-indicator"></div>
            <form id="searchForm">
                <input type="hidden" name="IDNumber" id="IDNumber" placeholder="ID Number" required readonly>
                <div class="scanned-id" id="scanned-id">
                    <i class="fas fa-info-circle"></i> 
                    <span>Align QR code within the frame to scan</span>
                </div>
                <div id="error-message" class="error-message" style="display: none;">
                    <i class="fas fa-exclamation-circle"></i> 
                    <span>Error: Camera access not available.</span>
                </div>
            </form>
        </div>

        <div class="action-buttons">
            <a href="search.php" class="headline-button">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Search</span>
            </a>
        </div>

    </div>

    <div class="confirm-dialog">
        <p>Are you sure you want to clear all scan history?</p>
        <div class="confirm-actions">
            <button class="confirm-button cancel">Cancel</button>
            <button class="confirm-button confirm">Clear All</button>
        </div>
    </div>

    <script>
        let scanner = null;
        let cameras = [];
        let currentCamera = 0;
        const video = document.getElementById('preview');
        const loadingOverlay = document.querySelector('.loading-overlay');
        const successAnimation = document.querySelector('.success-animation');
        let scanCount = parseInt(localStorage.getItem('scanCount') || '0');

        // Update initial scan count display
        document.getElementById('scanCount').textContent = scanCount;

        // Add click handler for scan counter
        document.getElementById('scanCounterBtn').addEventListener('click', function() {
            this.classList.toggle('active');
            toggleHistory();
        });

        // Initialize scan history from localStorage
        let scanHistory = JSON.parse(localStorage.getItem('scanHistory') || '[]');

        // Update real-time clock
        function updateRealTime() {
            const now = new Date();
            const hours = now.getHours();
            const minutes = now.getMinutes();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const formattedHours = hours % 12 || 12;
            const formattedMinutes = minutes.toString().padStart(2, '0');
            
            const realTimeElement = document.getElementById('realTime');
            if (realTimeElement) {
                realTimeElement.innerHTML = `${formattedHours}:${formattedMinutes}<span class="ampm">${ampm}</span>`;
            }
        }

        // Update clock every second
        setInterval(updateRealTime, 1000);
        updateRealTime(); // Initial update

        function showLoading() {
            if (loadingOverlay) loadingOverlay.classList.add('active');
        }

        function hideLoading() {
            if (loadingOverlay) loadingOverlay.classList.remove('active');
        }

        function showSuccess() {
            if (successAnimation) {
                successAnimation.classList.add('active');
                setTimeout(() => {
                    successAnimation.classList.remove('active');
                }, 2000);
            }
        }

        // Function to update all displays
        function updateDisplays() {
            // Update counter
            const scanCount = scanHistory.length;
            document.getElementById('scanCount').textContent = scanCount;

            // Update history title
            const historyTitle = document.querySelector('.history-title');
            if (historyTitle) {
                historyTitle.innerHTML = `
                    <i class="fas fa-history"></i>
                    Recent Scans (${scanCount})
                `;
            }

            // Update history display
            updateHistoryDisplay();
        }

        // Function to add scan to history
        function addToHistory(content) {
            const timestamp = new Date();
            const scanEntry = {
                content: content,
                time: timestamp.toLocaleTimeString(),
                date: timestamp.toLocaleDateString(),
                timestamp: timestamp.getTime()
            };

            scanHistory.unshift(scanEntry);
            
            // Keep only last 50 scans
            if (scanHistory.length > 50) {
                scanHistory = scanHistory.slice(0, 50);
            }
            
            // Save to localStorage
            localStorage.setItem('scanHistory', JSON.stringify(scanHistory));
            
            // Update all displays
            updateDisplays();
        }

        // Function to update history display
        function updateHistoryDisplay() {
            const historyList = document.getElementById('historyList');
            if (!historyList) return;

            if (scanHistory.length === 0) {
                historyList.innerHTML = '<div class="no-history">No scan history available</div>';
                return;
            }

            historyList.innerHTML = scanHistory.map((scan, index) => `
                <li class="history-item" data-index="${index}">
                    <div class="history-item-content">
                        <div class="history-item-id">${scan.content}</div>
                        <small class="history-item-time">${scan.date} ${scan.time}</small>
                    </div>
                    <div class="history-item-actions">
                        <button class="action-button" onclick="window.location.href='search.php?IDNumber=${encodeURIComponent(scan.content)}'">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="action-button delete" onclick="deleteScanHistory(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </li>
            `).join('');
        }

        // Function to delete single history item
        function deleteScanHistory(index) {
            const item = document.querySelector(`.history-item[data-index="${index}"]`);
            if (item) {
                item.classList.add('deleting');
                setTimeout(() => {
                    scanHistory.splice(index, 1);
                    localStorage.setItem('scanHistory', JSON.stringify(scanHistory));
                    updateDisplays();
                }, 300);
            }
        }

        // Function to clear all history
        function clearAllHistory() {
            scanHistory = [];
            localStorage.setItem('scanHistory', JSON.stringify(scanHistory));
            updateDisplays();
            hideConfirmDialog();
        }

        // Show/Hide confirm dialog
        function showConfirmDialog() {
            const dialog = document.querySelector('.confirm-dialog');
            if (dialog) dialog.classList.add('active');
        }

        function hideConfirmDialog() {
            const dialog = document.querySelector('.confirm-dialog');
            if (dialog) dialog.classList.remove('active');
        }

        // Toggle history panel
        function toggleHistory() {
            const history = document.querySelector('.scan-history');
            if (history) {
                history.classList.toggle('active');
                // Force reflow to ensure transition works
                history.offsetHeight;
                
                if (history.classList.contains('active')) {
                    updateHistoryDisplay();
                    // Prevent body scroll when history is open
                    document.body.style.overflow = 'hidden';
                } else {
                    // Re-enable body scroll when history is closed
                    document.body.style.overflow = '';
                }
            }
        }

        async function initializeScanner() {
            showLoading();
            try {
                scanner = new Instascan.Scanner({
                    video: video,
                    mirror: false,
                    continuous: true,
                    captureImage: true,
                    backgroundScan: false,
                    refractoryPeriod: 5000,
                    scanPeriod: 1
                });

                scanner.addListener('scan', handleScan);

                const cameras = await Instascan.Camera.getCameras();
                
                if (cameras.length === 0) {
                    throw new Error('No cameras found');
                }

                // Try to find the back camera
                let selectedCamera = cameras[0]; // Default to first camera
                for (let camera of cameras) {
                    if (camera.name.toLowerCase().includes('back') || 
                        camera.name.toLowerCase().includes('rear') || 
                        camera.name.toLowerCase().includes('environment')) {
                        selectedCamera = camera;
                        break;
                    }
                }

                await scanner.start(selectedCamera);
                console.log('Started scanner with camera:', selectedCamera.name);
                hideLoading();
                updateStatusBadge(true);

            } catch (error) {
                console.error('Scanner initialization error:', error);
                hideLoading();
                showError('Camera initialization failed: ' + error.message);
                updateStatusBadge(false);
            }
        }

        function handleScan(content) {
            if (!content) return;
            
            console.log('QR Code scanned:', content);
            showSuccess();
            
            // Add to scan history (this will update the counter)
            addToHistory(content);

            // Update scanned ID display
            const scannedId = document.getElementById('scanned-id');
            if (scannedId) {
                scannedId.innerHTML = `
                    <i class="fas fa-check-circle"></i>
                    <span>ID Scanned: ${content}</span>
                `;
                scannedId.classList.add('success');
            }

            // Set the ID in the hidden input
            const idInput = document.getElementById('IDNumber');
            if (idInput) {
                idInput.value = content;
            }

            // Redirect after a short delay
            setTimeout(() => {
                window.location.href = 'search.php?IDNumber=' + encodeURIComponent(content);
            }, 1500);
        }

        function showError(message) {
            const errorElement = document.getElementById('error-message');
            if (errorElement) {
                errorElement.innerHTML = `
                    <i class="fas fa-exclamation-circle"></i>
                    <span>${message}</span>
                `;
                errorElement.style.display = 'block';
            }
        }

        function updateStatusBadge(isActive) {
            const badge = document.querySelector('.status-badge');
            if (badge) {
                if (isActive) {
                    badge.classList.add('camera-active');
                } else {
                    badge.classList.remove('camera-active');
                }
            }
        }

        // Switch camera function
        document.getElementById('switchCamera').addEventListener('click', async () => {
            if (!scanner) return;
            
            try {
                const cameras = await Instascan.Camera.getCameras();
                if (cameras.length > 1) {
                    currentCamera = (currentCamera + 1) % cameras.length;
                    await scanner.start(cameras[currentCamera]);
                    console.log('Switched to camera:', cameras[currentCamera].name);
                }
            } catch (error) {
                console.error('Error switching camera:', error);
                showError('Failed to switch camera');
            }
        });

        // Initialize scanner when page loads
        document.addEventListener('DOMContentLoaded', () => {
            initializeScanner();
            updateDisplays();

            // History toggle button
            const toggleHistoryBtn = document.getElementById('toggleHistory');
            if (toggleHistoryBtn) {
                toggleHistoryBtn.addEventListener('click', toggleHistory);
            }

            // Clear all history button
            const clearAllBtn = document.querySelector('.history-button.clear-all');
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', showConfirmDialog);
            }

            // Confirm dialog buttons
            const confirmBtn = document.querySelector('.confirm-button.confirm');
            const cancelBtn = document.querySelector('.confirm-button.cancel');
            if (confirmBtn) confirmBtn.addEventListener('click', clearAllHistory);
            if (cancelBtn) cancelBtn.addEventListener('click', hideConfirmDialog);

            // Close history button
            const closeHistoryBtn = document.querySelector('.history-close');
            if (closeHistoryBtn) {
                closeHistoryBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const history = document.querySelector('.scan-history');
                    if (history) {
                        history.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            }

            // Prevent touchmove on scanner container when history is open
            document.querySelector('.scan-history').addEventListener('touchmove', function(e) {
                e.stopPropagation();
            }, { passive: true });

            // Close history when clicking outside
            document.addEventListener('click', function(e) {
                const history = document.querySelector('.scan-history');
                const scanCounter = document.getElementById('scanCounterBtn');
                
                if (history && history.classList.contains('active') && 
                    !history.contains(e.target) && 
                    !scanCounter.contains(e.target)) {
                    toggleHistory();
                }
            });
        });

        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                if (scanner) {
                    scanner.stop();
                    updateStatusBadge(false);
                }
            } else {
                if (scanner) {
                    initializeScanner();
                }
            }
        });

        // Update real-time clock
        function updateClock() {
            const timeElement = document.getElementById('realTime');
            if (timeElement) {
                const now = new Date();
                const timeString = now.toLocaleTimeString();
                timeElement.textContent = timeString;
            }
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>
