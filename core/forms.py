from django import forms
from django.contrib.auth.forms import UserCreationForm
from .models import User, UserProfile

class RegistroForm(UserCreationForm):
    class Meta:
        model = User
        fields = ['username', 'email']

class PerfilForm(forms.ModelForm):
    class Meta:
        model = UserProfile
        fields = ['avatar', 'banner', 'accent_color', 'bio']