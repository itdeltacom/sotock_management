@extends('site.layouts.app')

@section('content')
    <div class="bg-light rounded p-4 mb-4">
        <h1 class="display-6 text-primary mb-4">Mon Profil</h1>
        <p>Gérez vos informations personnelles et paramètres de compte.</p>
    </div>

    <div class="row g-4">
        <!-- Personal Information -->
        <div class="col-lg-6">
            <div class="bg-light rounded p-4">
                <h4 class="text-primary mb-4">Informations Personnelles</h4>
                <form id="profile-form" enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-12 text-center mb-3">
                            <div class="position-relative d-inline-block">
                                @if($user->photo)
                                    <img src="{{ Storage::url($user->photo) }}" alt="Profile Photo" class="rounded-circle"
                                        width="100" height="100" id="preview-photo">
                                @else
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                        style="width: 100px; height: 100px;" id="preview-icon">
                                        <i class="fa fa-user fa-3x text-primary"></i>
                                    </div>
                                    <img src="#" alt="Profile Photo" class="rounded-circle d-none" width="100" height="100"
                                        id="preview-photo">
                                @endif
                                <div class="position-absolute bottom-0 end-0">
                                    <label for="photo" class="btn btn-sm btn-primary rounded-circle"
                                        style="width: 32px; height: 32px;">
                                        <i class="fas fa-camera"></i>
                                    </label>
                                    <input type="file" id="photo" name="photo" class="d-none"
                                        accept="image/jpeg,image/png,image/jpg">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom Complet</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                        <div class="invalid-feedback" id="name-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}"
                            required>
                        <div class="invalid-feedback" id="email-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Téléphone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="{{ $user->phone }}">
                        <div class="invalid-feedback" id="phone-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Adresse</label>
                        <textarea class="form-control" id="address" name="address" rows="3">{{ $user->address }}</textarea>
                        <div class="invalid-feedback" id="address-feedback"></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">Mettre à jour le profil</button>
                </form>
            </div>
        </div>

        <!-- Account Security -->
        <div class="col-lg-6">
            <div class="bg-light rounded p-4">
                <h4 class="text-primary mb-4">Sécurité du Compte</h4>
                <form id="password-form">
                    @csrf
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mot de passe actuel</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                required>
                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                data-target="current_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="current-password-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Nouveau mot de passe</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="password-feedback"></div>
                        <div class="form-text">Le mot de passe doit comporter au moins 8 caractères.</div>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                data-target="password_confirmation">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="password-confirmation-feedback"></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">Changer le mot de passe</button>
                </form>
            </div>

            <!-- Account Information -->
            <div class="bg-light rounded p-4 mt-4">
                <h4 class="text-primary mb-4">Informations du Compte</h4>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Membre depuis</span>
                        <span class="fw-bold">{{ $user->created_at->format('d/m/Y') }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Dernière connexion</span>
                        <span class="fw-bold">{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Réservations totales</span>
                        <span class="fw-bold">{{ App\Models\Booking::where('user_id', $user->id)->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Handle photo preview
            $('#photo').change(function () {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        $('#preview-icon').addClass('d-none');
                        $('#preview-photo').attr('src', e.target.result).removeClass('d-none');
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Toggle password visibility
            $('.toggle-password').click(function () {
                const targetId = $(this).data('target');
                const input = $('#' + targetId);
                const icon = $(this).find('i');

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Handle profile form submission
            $('#profile-form').submit(function (e) {
                e.preventDefault();

                // Reset validation errors
                $(this).find('.is-invalid').removeClass('is-invalid');

                const formData = new FormData(this);

                $.ajax({
                    url: "{{ route('client.profile.update') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    },
                    error: function (xhr) {
                        const response = xhr.responseJSON;
                        if (response && response.errors) {
                            $.each(response.errors, function (key, value) {
                                $('#' + key).addClass('is-invalid');
                                $('#' + key + '-feedback').text(value[0]);
                            });
                        }
                    }
                });
            });

            // Handle password form submission
            $('#password-form').submit(function (e) {
                e.preventDefault();

                // Reset validation errors
                $(this).find('.is-invalid').removeClass('is-invalid');

                $.ajax({
                    url: "{{ route('client.password.update') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 2000
                            });

                            // Reset form
                            $('#password-form')[0].reset();
                        }
                    },
                    error: function (xhr) {
                        const response = xhr.responseJSON;
                        if (response && response.errors) {
                            $.each(response.errors, function (key, value) {
                                $('#' + key).addClass('is-invalid');
                                $('#' + key + '-feedback').text(value[0]);
                            });
                        } else if (response && response.message) {
                            $('#current_password').addClass('is-invalid');
                            $('#current-password-feedback').text(response.message);
                        }
                    }
                });
            });

            // Real-time validation for password confirmation
            $('#password_confirmation').on('input', function () {
                if ($(this).val() !== $('#password').val()) {
                    $(this).addClass('is-invalid');
                    $('#password-confirmation-feedback').text('Les mots de passe ne correspondent pas.');
                } else {
                    $(this).removeClass('is-invalid');
                    $('#password-confirmation-feedback').text('');
                }
            });
        });
    </script>
@endpush