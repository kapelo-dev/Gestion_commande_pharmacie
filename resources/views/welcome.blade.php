<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Pharma') }}</title>
        <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Styles -->
            <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #8B5CF6, #D946EF);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .navbar {
            padding: 1rem 2rem;
            display: flex;
            justify-content: flex-end;
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .login-btn {
            background-color: white;
            color: #8B5CF6;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .login-btn:hover {
            background-color: transparent;
            border-color: white;
            color: white;
        }

        .hero {
            flex: 1;
            display: flex;
            flex-direction: row-reverse;
            align-items: center;
            justify-content: space-between;
            padding: 2rem 5%;
            gap: 2rem;
            height: calc(100vh - 72px);
        }

        .hero-content {
            flex: 1;
            color: white;
            max-width: 500px;
            padding: 2rem;
            margin-left: auto;
        }

        .hero-content h1 {
            font-size: 2.8rem;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .hero-image {
            flex: 1;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            max-width: 50%;
        }

        .hero-image img {
            max-height: calc(100vh - 120px);
            width: auto;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 1024px) {
            .hero {
                flex-direction: column;
                text-align: center;
                padding: 1rem;
                height: calc(100vh - 72px);
                overflow-y: auto;
            }

            .hero-content {
                padding: 1rem;
                margin-left: 0;
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .hero-image {
                max-width: 100%;
                justify-content: center;
            }

            .hero-image img {
                max-height: 50vh;
                width: auto;
            }
        }
            </style>
    </head>
<body>
    <nav class="navbar">
        <a href="{{ route('login') }}" class="login-btn">Se connecter</a>
                </nav>

    <main class="hero">
        <div class="hero-content">
            <h1>Bienvenue dans votre espace de gestion pharmaceutique</h1>
            <p>Une solution moderne et intuitive pour gérer efficacement votre pharmacie. Simplifiez vos opérations quotidiennes et optimisez votre gestion.</p>
                </div>
        <div class="hero-image">
            <img src="{{ asset('images/ok2.jpg') }}" alt="Pharmacienne professionnelle">
        </div>
    </main>
    </body>
</html>
