<?php
$memory = [
    '0x0000' => 'LOAD A, 0x0A',
    '0x0001' => 'ADD B',
    '0x0002' => 'STORE 0x0C',
    '0x0003' => 'JUMP 0x00',
    '0x0004' => 'HALT',
    '0x0005' => 'NOP',
    '0x0006' => 'SUB A, 0x05',
    '0x0007' => 'AND B, 0x0F',
    '0x0008' => 'OR C, 0x10',
    '0x0009' => 'XOR D, 0x11'
];
$state = [
    'pc' => '0x0000',
    'ir' => '',
    'mar' => '',
    'mdr' => '',
    'step' => 1
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador de Ciclo Fetch</title>
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --gray: #95a5a6;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .simulator {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .component {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .component::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }
        
        .cpu::before {
            background-color: var(--primary);
        }
        
        .memory::before {
            background-color: var(--secondary);
        }
        
        .bus::before {
            background-color: var(--warning);
        }
        
        h2 {
            margin-bottom: 15px;
            color: var(--dark);
            display: flex;
            align-items: center;
        }
        
        h2 i {
            margin-right: 10px;
            font-size: 1.2em;
        }
        
        .registers {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .register {
            background-color: var(--light);
            padding: 15px;
            border-radius: 8px;
        }
        
        .register-name {
            font-weight: bold;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .register-value {
            font-family: 'Courier New', monospace;
            background-color: white;
            padding: 5px;
            border-radius: 4px;
            min-height: 1.5em;
        }
        
        .memory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
        }
        
        .memory-cell {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .memory-address {
            font-size: 0.8em;
            color: var(--gray);
            margin-bottom: 5px;
        }
        
        .memory-value {
            font-weight: bold;
        }
        
        .bus {
            height: 4px;
            background-color: var(--gray);
            margin: 20px 0;
            position: relative;
        }
        
        .bus-label {
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            background-color: white;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        
        button {
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-secondary {
            background-color: var(--secondary);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .step-info {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 4px solid var(--warning);
        }
        
        .step-title {
            color: var(--warning);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .active {
            box-shadow: 0 0 0 2px var(--warning);
            background-color: rgba(243, 156, 18, 0.1);
        }
        
        .highlight {
            animation: highlight 1s;
        }
        
        @keyframes highlight {
            0% { background-color: rgba(243, 156, 18, 0.3); }
            100% { background-color: transparent; }
        }
        
        .pc-pointer {
            border: 2px solid var(--danger);
        }
        @media (max-width: 768px) {
            .registers {
                grid-template-columns: 1fr;
            }
            
            .memory-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Simulador del Ciclo Fetch</h1>
            <p>Visualización paso a paso del proceso de búsqueda de instrucciones</p>
        </header>
        
        <div class="simulator">
            <div class="component cpu">
                <h2><i>⚙️</i> CPU - Unidad Central de Procesamiento</h2>
                <div class="registers">
                    <div class="register">
                        <div class="register-name">Contador de Programa (PC)</div>
                        <div class="register-value" id="pc-value"><?= $state['pc'] ?></div>
                    </div>
                    <div class="register">
                        <div class="register-name">Registro de Instrucción (IR)</div>
                        <div class="register-value" id="ir-value"><?= $state['ir'] ?: '-' ?></div>
                    </div>
                    <div class="register">
                        <div class="register-name">Registro de Dirección (MAR)</div>
                        <div class="register-value" id="mar-value"><?= $state['mar'] ?: '-' ?></div>
                    </div>
                    <div class="register">
                        <div class="register-name">Registro de Datos (MDR)</div>
                        <div class="register-value" id="mdr-value"><?= $state['mdr'] ?: '-' ?></div>
                    </div>
                </div>
            </div>
            <div class="bus">
                <div class="bus-label">Bus de Direcciones</div>
            </div>
            <div class="component memory">
                <h2><i>🧠</i> Memoria Principal</h2>
                <div class="memory-grid" id="memory-grid">
                    <?php foreach ($memory as $address => $value): ?>
                        <div class="memory-cell" data-address="<?= $address ?>">
                            <div class="memory-address"><?= $address ?></div>
                            <div class="memory-value"><?= $value ?></div>
                        </div>
                    <?php endforeach; ?>
                    <?php for ($i = count($memory); $i < 16; $i++): ?>
                        <div class="memory-cell" data-address="<?= sprintf('0x%04X', $i) ?>">
                            <div class="memory-address"><?= sprintf('0x%04X', $i) ?></div>
                            <div class="memory-value">0x00</div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="bus">
                <div class="bus-label">Bus de Datos</div>
            </div>
        </div>
        <div class="controls">
            <button class="btn-primary" id="next-step">
                <span>▶️</span> Siguiente paso
            </button>
            <button class="btn-secondary" id="reset-sim">
                <span>🔄</span> Reiniciar
            </button>
        </div>
        <div class="step-info">
            <h3 class="step-title">Instrucciones:</h3>
            <p>Presiona "Siguiente paso" para comenzar la simulación del ciclo fetch.</p>
            <div id="step-description"></div>
        </div>
    </div>

    <script>
        const state = {
            step: 1,
            pc: 0,
            ir: '',
            mar: '',
            mdr: '',
            running: false
        };
        const elements = {
            pcValue: document.getElementById('pc-value'),
            irValue: document.getElementById('ir-value'),
            marValue: document.getElementById('mar-value'),
            mdrValue: document.getElementById('mdr-value'),
            memoryCells: document.querySelectorAll('.memory-cell'),
            nextBtn: document.getElementById('next-step'),
            resetBtn: document.getElementById('reset-sim'),
            stepDesc: document.getElementById('step-description')
        };
        const stepDescriptions = [
            "",
            "1. <strong>Enviar dirección</strong>: La CPU coloca la dirección del PC en el bus de direcciones y la guarda en el MAR.",
            "2. <strong>Leer memoria</strong>: La memoria lee la instrucción en la dirección especificada y la coloca en el bus de datos.",
            "3. <strong>Transferir instrucción</strong>: La CPU recoge la instrucción del bus de datos y la guarda en el MDR.",
            "4. <strong>Almacenar en IR</strong>: La instrucción se copia del MDR al IR para su decodificación, y el PC avanza a la siguiente dirección."
        ];
        function initSimulation() {
            resetSimulation();
            setupEventListeners();
        }
        function setupEventListeners() {
            elements.nextBtn.addEventListener('click', nextStep);
            elements.resetBtn.addEventListener('click', resetSimulation);
        }
        function nextStep() {
            resetStyles();
            const currentCell = elements.memoryCells[state.pc];
            const address = currentCell.getAttribute('data-address');
            const instruction = currentCell.querySelector('.memory-value').textContent;
            switch(state.step) {
                case 1:
                    step1(address);
                    break;
                case 2:
                    step2(currentCell, instruction);
                    break;
                case 3:
                    step3(instruction);
                    break;
                case 4:
                    step4(instruction);
                    break;
            }
            elements.stepDesc.innerHTML = stepDescriptions[state.step];
        }
        function step1(address) {
            document.querySelector('.cpu').classList.add('active');
            document.getElementById('pc-value').classList.add('highlight');
            document.getElementById('mar-value').classList.add('highlight');
            document.querySelector('.bus').classList.add('active');
            state.mar = address;
            elements.marValue.textContent = address;
            const currentCell = elements.memoryCells[state.pc];
            currentCell.classList.add('pc-pointer');
            state.step = 2;
        }
        function step2(cell, instruction) {
            document.querySelector('.memory').classList.add('active');
            cell.classList.add('active', 'highlight');
            document.querySelectorAll('.bus')[1].classList.add('active');
            document.getElementById('mdr-value').classList.add('highlight');
            state.mdr = instruction;
            elements.mdrValue.textContent = instruction;
            state.step = 3;
        }
        function step3(instruction) {
            document.querySelector('.cpu').classList.add('active');
            document.getElementById('mdr-value').classList.add('highlight');
            document.querySelectorAll('.bus')[1].classList.add('active');
            state.step = 4;
        }
        
        function step4(instruction) {
            document.querySelector('.cpu').classList.add('active');
            document.getElementById('ir-value').classList.add('highlight');
            document.getElementById('pc-value').classList.add('highlight');
            state.ir = instruction;
            elements.irValue.textContent = instruction;
            state.pc = (state.pc + 1) % elements.memoryCells.length;
            elements.pcValue.textContent = `0x${state.pc.toString(16).padStart(4, '0')}`;
            state.mar = '';
            state.mdr = '';
            elements.marValue.textContent = '-';
            elements.mdrValue.textContent = '-';
            state.step = 1;
        }
        
        function resetStyles() {
            document.querySelector('.cpu').classList.remove('active');
            document.querySelector('.memory').classList.remove('active');
            document.querySelectorAll('.bus').forEach(bus => bus.classList.remove('active'));
            
            document.querySelectorAll('.register-value').forEach(el => el.classList.remove('highlight'));
            document.querySelectorAll('.memory-cell').forEach(cell => {
                cell.classList.remove('active', 'highlight', 'pc-pointer');
            });
        }
        
        function resetSimulation() {
            state.step = 1;
            state.pc = 0;
            state.ir = '';
            state.mar = '';
            state.mdr = '';
            
            elements.pcValue.textContent = '0x0000';
            elements.irValue.textContent = '-';
            elements.marValue.textContent = '-';
            elements.mdrValue.textContent = '-';
            elements.stepDesc.innerHTML = 'Presiona "Siguiente paso" para comenzar la simulación del ciclo fetch.';
            resetStyles();
        }
        window.addEventListener('DOMContentLoaded', initSimulation);
    </script>
</body>
</html>