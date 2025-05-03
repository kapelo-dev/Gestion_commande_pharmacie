<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Skydash Admin</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}">
  <link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
  <!-- endinject -->
  <!-- inject:css -->
  <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
  <!-- endinject -->
  <link rel="shortcut icon" href="{{ asset('images/image.png') }}" />
  <style>
    body {
      background: #f4f5f7;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .auth-container {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      display: flex;
      width: 800px;
      height: 400px;
      overflow: hidden;
      border: 3px solid #6a11cb; /* Contour bleu-violet */
    }
    .logo-section {
      background: linear-gradient(to bottom, #6a11cb, #2575fc);
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .form-section {
      flex: 1;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .form-control {
      border-radius: 5px;
      padding: 10px;
      font-size: 16px;
      border: 1px solid #ddd;
      margin-bottom: 15px;
    }
    .auth-form-btn {
      background: #2517d5;
      border: none;
      border-radius: 5px;
      padding: 10px;
      font-size: 18px;
      color: #fff;
      cursor: pointer;
    }
    .auth-form-btn:hover {
      background: blue;
    }
    .brand-logo img {
      max-width: 80%;
      max-height: 80%;
    }
    .form-group label {
      color: #333;
      font-weight: bold;
    }
    .error-message {
      color: red;
      margin-bottom: 15px;
    }
  </style>
</head>

<body>
  <div class="container-scroller d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="auth-container">
      <!-- Left side for logo -->
      <div class="logo-section">
        <div class="brand-logo">
          <img src="{{ asset('images/Symbole-Pharmacie.png') }}" alt="logo"> <!-- Remplacez par le chemin de votre logo -->
        </div>
      </div>
      <!-- Right side for form -->
      <div class="form-section">
        <h1>Se connecter</h1>
        <p class="account-subtitle">Accéder à votre application</p>
        @if (session('error'))
          <div class="error-message">
            {{ session('error') }}
          </div>
        @endif
        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="form-group">
            <label for="identifiant">Identifiant</label>
            <input type="text" class="form-control @error('identifiant') is-invalid @enderror" id="identifiant" name="identifiant" placeholder="Identifiant" required autofocus>
            @error('identifiant')
            <span class="invalid-feedback" role="alert">
              <strong>{{ $message }}</strong>
            </span>
            @enderror
          </div>
          <div class="form-group">
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" class="form-control @error('mot_de_passe') is-invalid @enderror" id="mot_de_passe" name="mot_de_passe" placeholder="Mot de passe" required>
            @error('mot_de_passe')
            <span class="invalid-feedback" role="alert">
              <strong>{{ $message }}</strong>
            </span>
            @enderror
          </div>
          <button type="submit" class="auth-form-btn"> Se connecter</button>
        </form>
      </div>
    </div>
  </div>
  <!-- plugins:js -->
  <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
  <!-- endinject -->
  <!-- inject:js -->
  <script src="{{ asset('js/off-canvas.js') }}"></script>
  <script src="{{ asset('js/hoverable-collapse.js') }}"></script>
  <script src="{{ asset('js/template.js') }}"></script>
  <script src="{{ asset('js/settings.js') }}"></script>
  <script src="{{ asset('js/todolist.js') }}"></script>
  <!-- endinject -->
</body>

</html>
