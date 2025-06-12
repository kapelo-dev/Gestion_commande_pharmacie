<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Connexion - Pharma</title>
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
      background: linear-gradient(135deg, #8B5CF6, #D946EF);
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      padding: 20px;
    }
    .auth-container {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
      display: flex;
      width: 800px;
      height: 400px;
      overflow: hidden;
    }
    .logo-section {
      background: linear-gradient(135deg, #8B5CF6, #D946EF);
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    .logo-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(217, 70, 239, 0.2));
      backdrop-filter: blur(10px);
    }
    .form-section {
      flex: 1;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .form-section h1 {
      color: #333;
      margin-bottom: 10px;
      font-size: 28px;
      font-weight: 600;
    }
    .account-subtitle {
      color: #666;
      margin-bottom: 30px;
      font-size: 16px;
    }
    .form-control {
      border-radius: 10px;
      padding: 12px 15px;
      font-size: 16px;
      border: 1px solid #ddd;
      margin-bottom: 15px;
      transition: all 0.3s ease;
    }
    .form-control:focus {
      border-color: #8B5CF6;
      box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.2);
      outline: none;
    }
    .auth-form-btn {
      background: linear-gradient(135deg, #8B5CF6, #D946EF);
      border: none;
      border-radius: 10px;
      padding: 12px;
      font-size: 16px;
      color: #fff;
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: 500;
      margin-top: 10px;
    }
    .auth-form-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4);
    }
    .brand-logo img {
      max-width: 80%;
      max-height: 80%;
      position: relative;
      z-index: 1;
    }
    .form-group label {
      color: #333;
      font-weight: 500;
      margin-bottom: 8px;
      display: block;
    }
    .error-message {
      color: #ef4444;
      margin-bottom: 15px;
      padding: 10px;
      border-radius: 8px;
      background-color: #fef2f2;
      border: 1px solid #fee2e2;
    }
    @media (max-width: 768px) {
      .auth-container {
        flex-direction: column;
        height: auto;
        width: 90%;
      }
      .logo-section {
        padding: 40px 20px;
      }
      .form-section {
        padding: 30px 20px;
      }
    }
  </style>
</head>

<body>
  <div class="auth-container">
    <!-- Left side for logo -->
    <div class="logo-section">
      <div class="brand-logo">
        <img src="{{ asset('images/Symbole-Pharmacie.png') }}" alt="logo">
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
          <input type="text" class="form-control @error('identifiant') is-invalid @enderror" id="identifiant" name="identifiant" placeholder="Votre identifiant" required autofocus>
          @error('identifiant')
          <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
          </span>
          @enderror
        </div>
        <div class="form-group">
          <label for="mot_de_passe">Mot de passe</label>
          <input type="password" class="form-control @error('mot_de_passe') is-invalid @enderror" id="mot_de_passe" name="mot_de_passe" placeholder="Votre mot de passe" required>
          @error('mot_de_passe')
          <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
          </span>
          @enderror
        </div>
        <button type="submit" class="auth-form-btn">Se connecter</button>
      </form>
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
