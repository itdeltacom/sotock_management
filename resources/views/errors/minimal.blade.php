<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($title) ? $title : 'Erreur - Cental Location de Voitures' }}</title>
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('site/img/favicon.ico') }}" type="image/x-icon">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #F9A826;
            --secondary: #F2F2F4;
            --light: #FFFFFF;
            --dark: #212529;
        }
        
        body {
            font-family: 'Lato', sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .error-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .error-content {
            background-color: var(--light);
            border-radius: 10px;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        
        .error-icon {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        .error-code {
            font-size: 5rem;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            color: var(--primary);
            margin: 0;
            line-height: 1;
        }
        
        .error-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 1rem 0 1.5rem;
            font-family: 'Montserrat', sans-serif;
        }
        
        .error-message {
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .btn-primary {
            color: var(--light);
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary:hover {
            background-color: #e59113;
            border-color: #e59113;
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .logo h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            color: var(--primary);
            font-size: 2rem;
            margin: 0;
        }
        
        .contact-info {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }
        
        .contact-info h6 {
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--dark);
        }
        
        .contact-info p {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .contact-info i {
            color: var(--primary);
            margin-right: 0.5rem;
        }
        
        .footer {
            text-align: center;
            padding: 1rem;
            background-color: var(--secondary);
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        @media (max-width: 576px) {
            .error-content {
                padding: 2rem;
            }
            
            .error-code {
                font-size: 4rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <div class="logo">
                <h2><i class="fas fa-car-alt me-2"></i>Cental</h2>
            </div>
            
            @if(isset($errorCode))
                <div class="error-icon">
                    @switch($errorCode)
                        @case(401)
                            <i class="bi bi-shield-lock"></i>
                            @break
                        @case(403)
                            <i class="bi bi-x-octagon"></i>
                            @break
                        @case(404)
                            <i class="bi bi-exclamation-triangle"></i>
                            @break
                        @case(419)
                            <i class="bi bi-hourglass-split"></i>
                            @break
                        @case(429)
                            <i class="bi bi-speedometer2"></i>
                            @break
                        @case(500)
                            <i class="bi bi-gear"></i>
                            @break
                        @case(503)
                            <i class="bi bi-tools"></i>
                            @break
                        @default
                            <i class="bi bi-exclamation-circle"></i>
                    @endswitch
                </div>
                <h1 class="error-code">{{ $errorCode }}</h1>
                <h2 class="error-title">{{ $errorTitle ?? 'Une erreur s\'est produite' }}</h2>
                <p class="error-message">{{ $errorMessage ?? 'Nous rencontrons actuellement un problème technique. Veuillez réessayer ultérieurement ou contacter notre service client.' }}</p>
            @else
                <div class="error-icon">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <h1 class="error-code">Erreur</h1>
                <h2 class="error-title">Oups ! Quelque chose s'est mal passé</h2>
                <p class="error-message">Nous rencontrons actuellement un problème technique. Veuillez réessayer ultérieurement ou contacter notre service client.</p>
            @endif
            
            <a href="{{ route('home') }}" class="btn-primary">Retour à l'Accueil</a>
            
            <div class="contact-info">
                <h6>Besoin d'assistance immédiate ?</h6>
                <p><i class="fas fa-phone-alt"></i>+212 5 22 XX XX XX</p>
                <p><i class="fas fa-envelope"></i>contact@cental.ma</p>
                <p><i class="fab fa-whatsapp"></i>+212 6 XX XX XX XX</p>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} Cental Location de Voitures. Tous droits réservés.</p>
    </div>
</body>
</html>