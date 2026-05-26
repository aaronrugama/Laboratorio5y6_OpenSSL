<?php
/*
 * Cifrado Simétrico Interactivo con Formulario Web
 * Algoritmo: AES-128-CBC mediante OpenSSL
 * Laboratorio: Seguridad en PHP con OpenSSL
 */

$resultado = null;
$error     = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mensaje = trim($_POST['mensaje'] ?? '');
    $clave   = $_POST['clave'] ?? '';

    //Validar que los campos no estén vacíos
    if ($mensaje === '' || $clave === '') {
        $error = "Ambos campos son obligatorios.";
    } else {
        //Normalizar la clave a exactamente 16 caracteres (truncar o rellenar)
        $claveNormalizada = str_pad(substr($clave, 0, 16), 16, '0');

        //Validar que el IV sea generado dinámicamente
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length("AES-128-CBC"));

        // Cifrar el mensaje
        $cifrado = openssl_encrypt($mensaje, "AES-128-CBC", $claveNormalizada, 0, $iv);

        //Descifrar inmediatamente para verificación
        $descifrado = openssl_decrypt($cifrado, "AES-128-CBC", $claveNormalizada, 0, $iv);

        $resultado = [
            'iv_hex'     => bin2hex($iv),       // IV en formato hexadecimal
            'cifrado'    => $cifrado,             // Texto cifrado en Base64
            'descifrado' => $descifrado,          // Resultado del descifrado
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cifrado AES-128-CBC | OpenSSL PHP</title>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ── Variables ───────────────────────────────────────── */
        :root {
            --bg:        #0f1117;
            --surface:   #1a1d27;
            --border:    #2e3347;
            --accent:    #00e5c3;
            --accent2:   #6c63ff;
            --text:      #e4e6f0;
            --muted:     #7a7f99;
            --danger:    #ff5f7e;
            --success:   #00e5c3;
            --mono:      'Share Tech Mono', monospace;
            --sans:      'Nunito', sans-serif;
            --radius:    10px;
        }

        /* ── Reset ───────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--sans);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 16px 60px;
        }

        /* ── Header ──────────────────────────────────────────── */
        header {
            text-align: center;
            margin-bottom: 36px;
        }
        .badge {
            display: inline-block;
            font-family: var(--mono);
            font-size: 0.72rem;
            color: var(--accent);
            border: 1px solid var(--accent);
            border-radius: 4px;
            padding: 3px 10px;
            letter-spacing: 0.12em;
            margin-bottom: 14px;
            text-transform: uppercase;
        }
        h1 {
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1.2;
        }
        h1 span { color: var(--accent); }
        .subtitle {
            color: var(--muted);
            font-size: 0.9rem;
            margin-top: 6px;
        }

        /* ── Card ────────────────────────────────────────────── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px 28px;
            width: 100%;
            max-width: 560px;
        }

        /* ── Form ────────────────────────────────────────────── */
        .field { margin-bottom: 20px; }
        label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 7px;
        }
        label span { color: var(--danger); }

        textarea, input[type="text"] {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text);
            font-family: var(--sans);
            font-size: 0.95rem;
            padding: 11px 14px;
            transition: border-color 0.2s;
            outline: none;
        }
        textarea { resize: vertical; min-height: 90px; }
        textarea:focus, input[type="text"]:focus {
            border-color: var(--accent);
        }

        .hint {
            font-size: 0.75rem;
            color: var(--muted);
            margin-top: 5px;
        }

        /* ── Button ──────────────────────────────────────────── */
        button[type="submit"] {
            width: 100%;
            padding: 13px;
            background: var(--accent);
            color: #0f1117;
            border: none;
            border-radius: var(--radius);
            font-family: var(--sans);
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.04em;
            transition: opacity 0.2s, transform 0.15s;
        }
        button[type="submit"]:hover  { opacity: 0.88; transform: translateY(-1px); }
        button[type="submit"]:active { transform: translateY(0); }

        /* ── Error ───────────────────────────────────────────── */
        .alert-error {
            background: rgba(255,95,126,0.12);
            border: 1px solid var(--danger);
            border-radius: var(--radius);
            color: var(--danger);
            font-size: 0.88rem;
            padding: 11px 14px;
            margin-bottom: 20px;
        }

        /* ── Results ─────────────────────────────────────────── */
        .results {
            margin-top: 28px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .result-block {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }
        .result-block-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            background: rgba(255,255,255,0.03);
            border-bottom: 1px solid var(--border);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: var(--muted);
        }
        .dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .dot-iv      { background: #6c63ff; }
        .dot-cifrado { background: #ff9f43; }
        .dot-ok      { background: var(--success); }

        .result-block-body {
            padding: 12px 14px;
            font-family: var(--mono);
            font-size: 0.82rem;
            color: var(--text);
            word-break: break-all;
            line-height: 1.6;
        }

        /* ── Footer ──────────────────────────────────────────── */
        footer {
            margin-top: 36px;
            font-size: 0.75rem;
            color: var(--muted);
            text-align: center;
        }
    </style>
</head>
<body>

<header>
    <div class="badge">AES-128-CBC · OpenSSL</div>
    <h1>Cifrado <span>Simétrico</span></h1>
    <p class="subtitle">Laboratorio · Seguridad en PHP con OpenSSL</p>
</header>

<div class="card">

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!--Formulario con textarea + input, método POST hacia sí mismo -->
    <form method="POST" action="">

        <div class="field">
            <label for="mensaje">Mensaje en Claro <span>*</span></label>
            <textarea
                id="mensaje"
                name="mensaje"
                placeholder="Escribe el texto que deseas proteger..."
            ><?= htmlspecialchars($_POST['mensaje'] ?? '') ?></textarea>
        </div>

        <div class="field">
            <label for="clave">Clave Secreta Compartida <span>*</span></label>
            <input
                type="text"
                id="clave"
                name="clave"
                placeholder="Ej: miClave2024"
                value="<?= htmlspecialchars($_POST['clave'] ?? '') ?>"
            >
            <p class="hint">Se normalizará automáticamente a 16 caracteres (AES-128).</p>
        </div>

        <button type="submit">Cifrar y Verificar →</button>

    </form>

    <!--Mostrar los tres bloques de resultado -->
    <?php if ($resultado): ?>
    <div class="results">

        <div class="result-block">
            <div class="result-block-header">
                <span class="dot dot-iv"></span>
                Vector de Inicialización (IV) — Hexadecimal
            </div>
            <div class="result-block-body">
                <?= htmlspecialchars($resultado['iv_hex']) ?>
            </div>
        </div>

        <div class="result-block">
            <div class="result-block-header">
                <span class="dot dot-cifrado"></span>
                Texto Cifrado — Base64
            </div>
            <div class="result-block-body">
                <?= htmlspecialchars($resultado['cifrado']) ?>
            </div>
        </div>

        <div class="result-block">
            <div class="result-block-header">
                <span class="dot dot-ok"></span>
                Resultado del Descifrado
            </div>
            <div class="result-block-body">
                <?= htmlspecialchars($resultado['descifrado']) ?>
            </div>
        </div>

    </div>
    <?php endif; ?>

</div>

<footer>
    Desarrollo de Software VII · Universidad Tecnológica de Panamá
    <br>
    Aaron Lopez 
    <br>
    kevyn Reyes
</footer>

</body>
</html>