from django.db import models
from django.contrib.auth.models import AbstractUser

class User(AbstractUser):
    avatar_url = models.URLField(max_length=500, blank=True, null=True)
    bio = models.TextField(blank=True, null=True)
    is_public = models.BooleanField(default=True)

class Game(models.Model):
    igdb_id = models.BigIntegerField(unique=True)
    title = models.CharField(max_length=255)
    cover_url = models.URLField(max_length=500, blank=True, null=True)
    slug = models.SlugField(max_length=255, unique=True)

class ActivityPost(models.Model):
    TYPE_CHOICES = (
        ('review', 'Reseña'),
        ('status', 'Estado'),
        ('screenshot', 'Captura'),
    )
    user = models.ForeignKey(User, on_delete=models.CASCADE, related_name='posts')
    game_title = models.CharField(max_length=255)
    game_cover = models.URLField(max_length=500)
    content = models.TextField()
    type = models.CharField(max_length=20, choices=TYPE_CHOICES, default='status')
    rating = models.IntegerField(default=0)
    image = models.ImageField(upload_to='activity_images/', blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    screenshot = models.ImageField(upload_to='screenshots/', null=True, blank=True)

    class Meta:
        ordering = ['-created_at']

class UserProfile(models.Model):
    user = models.OneToOneField(User, on_delete=models.CASCADE, related_name='profile')
    avatar = models.ImageField(upload_to='avatars/', null=True, blank=True)
    banner = models.ImageField(upload_to='banners/', null=True, blank=True)
    bio = models.TextField(max_length=1000, blank=True)
    accent_color = models.CharField(max_length=7, default='#3b82f6')
    location = models.CharField(max_length=100, blank=True)
    twitch_url = models.URLField(blank=True)
    steam_url = models.URLField(blank=True)
    twitter_url = models.URLField(blank=True)
    youtube_url = models.URLField(blank=True)

    def __str__(self):
        return self.user.username

class UserGame(models.Model):
    STATUS_CHOICES = [
        ('playing', 'Jugando'),
        ('finished', 'Terminado'),
        ('completed', 'Completado'),
        ('backlog', 'Backlog'),
        ('paused', 'Pausado'),
        ('abandoned', 'Abandonado'),
        ('needs_attention', 'Necesita Atención'),
    ]
    user = models.ForeignKey(User, on_delete=models.CASCADE, related_name='games')
    game_id = models.IntegerField()
    game_name = models.CharField(max_length=255)
    cover_url = models.URLField(blank=True, null=True)
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='backlog')
    rating = models.IntegerField(default=0)
    platform = models.CharField(max_length=100, blank=True, null=True, default='No especificada')
    hours = models.IntegerField(default=0)
    minutes = models.IntegerField(default=0)
    added_at = models.DateTimeField(auto_now_add=True)
    screenshot = models.ImageField(upload_to='screenshots/', null=True, blank=True)

    def __str__(self):
        return f"{self.user.username} - {self.game_name}"

class FavoriteGame(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE, related_name='favorite_games')
    slot = models.IntegerField()
    game_id = models.IntegerField()
    game_name = models.CharField(max_length=255)
    cover_url = models.URLField(max_length=500, blank=True, null=True)

    class Meta:
        unique_together = ('user', 'slot')

class Story(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE, related_name='stories')
    image = models.ImageField(upload_to='stories/')
    game_name = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ['-created_at']

class Follow(models.Model):
    follower = models.ForeignKey(User, related_name='following', on_delete=models.CASCADE)
    followed = models.ForeignKey(User, related_name='followers', on_delete=models.CASCADE)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        unique_together = ('follower', 'followed')