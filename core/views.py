import json
import traceback
from django.shortcuts import render, redirect,get_object_or_404
from django.contrib.auth import login, authenticate, logout
from django.contrib.auth.forms import AuthenticationForm
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.utils import timezone
from django.http import JsonResponse
from .forms import RegistroForm,PerfilForm
from datetime import timedelta
from .models import ActivityPost, User,UserProfile,UserGame,FavoriteGame,Story,Follow
from .utils import get_trending_games, search_igdb_games, get_igdb_game_details, get_upcoming_games
from django.db.models import Count

def feed(request):
    posts = ActivityPost.objects.all().select_related('user').order_by('-created_at')[:50]
    trending_games = get_trending_games()
    upcoming_games = get_upcoming_games()
    
    sugeridos = User.objects.exclude(id=request.user.id)[:3] if request.user.is_authenticated else User.objects.all()[:3]
    
    user_games_titles = []
    if request.user.is_authenticated:
        user_games_titles = list(UserGame.objects.filter(user=request.user).values_list('game_name', flat=True))

    time_limit = timezone.now() - timedelta(hours=24)
    active_stories = Story.objects.filter(created_at__gte=time_limit).select_related('user').order_by('created_at')
    sugeridos = User.objects.exclude(id=request.user.id).order_by('?')[:5]
    
    stories_data = {}
    for story in active_stories:
        username = story.user.username
        if username not in stories_data:
            avatar = story.user.profile.avatar.url if hasattr(story.user, 'profile') and getattr(story.user.profile, 'avatar', None) else ''
            stories_data[username] = {
                'username': username,
                'avatar': avatar,
                'items': [],
                'latest_id': 0
            }
        
        stories_data[username]['items'].append({
            'id': story.id,
            'url': story.image.url,
            'game': story.game_name or ''
        })
        
        if story.id > stories_data[username]['latest_id']:
            stories_data[username]['latest_id'] = story.id

    stories_list = list(stories_data.values())

    return render(request, 'core/feed.html', {
        'posts': posts,
        'trending_games': trending_games,
        'upcoming_games': upcoming_games,
        'sugeridos': sugeridos,
        'user_games': json.dumps(user_games_titles),
        'stories_list': stories_list,
        'stories_json': json.dumps(stories_data)
    })

def search(request):
    # Si la petición es en tiempo real (AJAX) desde Javascript
    if request.GET.get('ajax') == '1':
        query = request.GET.get('q', '').strip()
        
        # BÚSQUEDA DE USUARIOS (Si empieza por @)
        if query.startswith('@'):
            username_query = query[1:] # Quitamos la arroba
            users = User.objects.filter(username__icontains=username_query)[:10]
            
            results = []
            for u in users:
                profile = getattr(u, 'profile', None)
                avatar_url = profile.avatar.url if profile and profile.avatar else ''
                results.append({
                    'type': 'user',
                    'username': u.username,
                    'avatar_url': avatar_url,
                })
            return JsonResponse({'results': results, 'search_type': 'user'})
            
        # BÚSQUEDA DE JUEGOS
        else:
            if len(query) >= 2:
                # Usamos tu función existente de utilidades
                sort = request.GET.get('sort', 'popular')
                games = search_igdb_games(query, sort)
                return JsonResponse({'results': games, 'search_type': 'game'})
            return JsonResponse({'results': [], 'search_type': 'game'})

    # Si es una carga normal de la página, solo devolvemos la plantilla HTML
    return render(request, 'core/search.html')

def game_detail(request, game_id):
    game = get_igdb_game_details(game_id)
    
    if game is None:
        messages.error(request, "No se han podido sincronizar los datos de este juego.")
        return redirect('feed')

    user_screenshots = ActivityPost.objects.filter(
        game_title=game['name'], 
        type='screenshot'
    ).exclude(image='')
    
    game_reviews = ActivityPost.objects.filter(
        game_title=game['name'], 
        type='review'
    ).select_related('user')

    # CORRECCIÓN AQUÍ: Excluimos tanto los NULL como los strings vacíos
    game_screenshots = ActivityPost.objects.filter(
        game_title=game['name']
    ).exclude(screenshot__isnull=True).exclude(screenshot__exact='').order_by('-created_at')

    return render(request, 'core/game_detail.html', {
        'game': game,
        'user_screenshots': user_screenshots,
        'game_reviews': game_reviews,
        'game_screenshots': game_screenshots,
        'states': [
            ('playing', 'Jugando', 'fa-gamepad'),
            ('completed', 'Terminado', 'fa-trophy'),
            ('backlog', 'Pendiente', 'fa-bookmark'),
            ('dropped', 'Abandonado', 'fa-skull'),
        ]
    })

def registro_view(request):
    if request.method == 'POST':
        form = RegistroForm(request.POST)
        if form.is_valid():
            user = form.save()
            login(request, user)
            messages.success(request, f"¡Bienvenido a Gamebox, {user.username}!")
            return redirect('feed')
    else:
        form = RegistroForm()
    return render(request, 'core/auth/registro.html', {'form': form})

def login_view(request):
    if request.method == 'POST':
        form = AuthenticationForm(request, data=request.POST)
        if form.is_valid():
            username = form.cleaned_data.get('username')
            password = form.cleaned_data.get('password')
            user = authenticate(username=username, password=password)
            if user is not None:
                login(request, user)
                return redirect('feed')
    else:
        form = AuthenticationForm()
    return render(request, 'core/auth/login.html', {'form': form})

def logout_view(request):
    logout(request)
    return redirect('feed')

@login_required
def add_to_backlog(request, game_id):
    if request.method == 'POST':
        game_data = get_igdb_game_details(game_id)
        
        # Obtenemos los datos del modal
        status = request.POST.get('status', 'backlog')
        platform = request.POST.get('platform', 'No especificada')
        
        # Mapeo de nombres para que el texto sea bonito en el feed
        status_text = {
            'playing': 'está jugando a',
            'completed': 'ha terminado',
            'backlog': 'ha añadido al backlog',
            'dropped': 'ha abandonado'
        }

        if game_data:
            # Creamos el post con los detalles del modal
            ActivityPost.objects.create(
                user=request.user,
                game_title=game_data['name'],
                game_cover=game_data['cover']['url'],
                content=f"{status_text.get(status)} {game_data['name']} en {platform}.",
                type='status'
            )
            return JsonResponse({'status': 'success'})
            
    return JsonResponse({'status': 'error'}, status=400)

@login_required
def post_review(request, game_id):
    if request.method == 'POST':
        game_data = get_igdb_game_details(game_id)
        if game_data:
            content = request.POST.get('content')
            rating = request.POST.get('rating', 0)
            image = request.FILES.get('screenshot')
            
            post_type = 'screenshot' if image else 'review'

            ActivityPost.objects.create(
                user=request.user,
                game_title=game_data['name'],
                game_cover=game_data['cover']['url'],
                content=content,
                rating=rating,
                image=image,
                type=post_type
            )
            messages.success(request, "¡Actividad transmitida a la red!")
            return redirect('feed')
    return redirect('game_detail', game_id=game_id)

def profile_view(request, username):
    profile_user = get_object_or_404(User, username=username)
    profile, created = UserProfile.objects.get_or_create(user=profile_user)

    if request.method == 'POST' and request.user == profile_user:
        profile.bio = request.POST.get('bio', profile.bio)
        profile.location = request.POST.get('location', profile.location)
        profile.accent_color = request.POST.get('accent_color', profile.accent_color)
        profile.twitch_url = request.POST.get('twitch_url', profile.twitch_url)
        profile.steam_url = request.POST.get('steam_url', profile.steam_url)
        profile.twitter_url = request.POST.get('twitter_url', profile.twitter_url)
        profile.youtube_url = request.POST.get('youtube_url', profile.youtube_url)
        
        if 'avatar' in request.FILES:
            profile.avatar = request.FILES['avatar']
        if 'banner' in request.FILES:
            profile.banner = request.FILES['banner']
            
        profile.save()
        return redirect('profile', username=username)

    user_reviews = ActivityPost.objects.filter(user=profile_user, type__in=['review', 'screenshot']).order_by('-created_at')
    
    is_following = False
    if request.user.is_authenticated and request.user != profile_user:
        is_following = Follow.objects.filter(follower=request.user, followed=profile_user).exists()

    user_games = UserGame.objects.filter(user=profile_user).order_by('-added_at')
    
    total_h = sum(g.hours for g in user_games)
    total_m = sum(g.minutes for g in user_games)
    total_h += total_m // 60
    
    stats = {
        'total_games': user_games.count(),
        'playing': user_games.filter(status='playing').count(),
        'finished': user_games.filter(status='finished').count(),
        'completed': user_games.filter(status='completed').count(),
        'backlog': user_games.filter(status='backlog').count(),
        'paused': user_games.filter(status='paused').count(),
        'abandoned': user_games.filter(status='abandoned').count(),
        'achievements': 0,
        'playtime': f"{total_h}h",
        'verified': '100%',
        'following': profile_user.following.count(),
        'followers': profile_user.followers.count(),
    }

    fav_list = []
    for i in range(1, 6):
        fav = FavoriteGame.objects.filter(user=profile_user, slot=i).first()
        fav_list.append({'slot': i, 'game': fav})

    context = {
        'profile_user': profile_user,
        'profile': profile,
        'stats': stats,
        'user_games': user_games,
        'user_reviews': user_reviews,
        'is_following': is_following,
        'fav_list': fav_list,
        'current_year': 2026,
    }
    return render(request, 'core/profile.html', context)

@login_required
def add_game(request, game_id):
    if request.method == 'POST':
        try:
            game_name = request.POST.get('game_name', 'Juego Desconocido')
            cover_url = request.POST.get('cover_url', '')
            status = request.POST.get('status', 'playing')
            platform = request.POST.get('platform', 'Sin especificar')
            
            rating_val = request.POST.get('rating')
            rating = int(rating_val) if rating_val and rating_val.isdigit() else 0
            
            hours_val = request.POST.get('hours')
            hours = int(hours_val) if hours_val and hours_val.isdigit() else 0
            
            minutes_val = request.POST.get('minutes')
            minutes = int(minutes_val) if minutes_val and minutes_val.isdigit() else 0
            
            review_title = request.POST.get('review_title', '')
            review_text = request.POST.get('review_text', '')
            screenshot = request.FILES.get('screenshot')

            user_game, created = UserGame.objects.update_or_create(
                user=request.user,
                game_id=game_id,
                defaults={
                    'game_name': game_name, 
                    'cover_url': cover_url,
                    'status': status, 
                    'rating': rating, 
                    'platform': platform,
                    'hours': hours, 
                    'minutes': minutes
                }
            )

            if screenshot:
                user_game.screenshot = screenshot
                user_game.save()

            status_dict = {
                'playing': 'está jugando a',
                'finished': 'ha terminado',
                'completed': 'ha completado al 100%',
                'backlog': 'ha añadido a su backlog',
                'abandoned': 'ha abandonado'
            }
            status_msg = status_dict.get(status, 'ha actualizado')

            if review_text.strip():
                post_type = 'review'
                content_text = f"{review_title}\n\n{review_text}".strip() if review_title else review_text.strip()
            elif screenshot:
                post_type = 'screenshot'
                content_text = f"Ha compartido una nueva captura de pantalla de {game_name}."
            else:
                post_type = 'update'
                content_text = f"{status_msg} {game_name}."

            # AQUI ESTABA EL ERROR. Hemos quitado game_id=game_id para que no rompa tu base de datos.
            ActivityPost.objects.create(
                user=request.user, 
                game_title=game_name, 
                game_cover=cover_url,
                content=content_text,
                type=post_type, 
                rating=rating,
                screenshot=screenshot
            )
            
            return JsonResponse({'status': 'success'})
        except Exception as e:
            print("=== ERROR AL GUARDAR JUEGO ===")
            traceback.print_exc()
            return JsonResponse({'status': 'error', 'message': str(e)}, status=500)
            
    return JsonResponse({'status': 'error'})

@login_required
def delete_game(request, game_id):
    if request.method == 'POST':
        # Buscamos el juego del usuario y lo eliminamos
        UserGame.objects.filter(user=request.user, game_id=game_id).delete()
        return JsonResponse({'status': 'success'})
    return JsonResponse({'status': 'error'})

@login_required
def search_fav_api(request):
    query = request.GET.get('q', '')
    results = []
    if query:
        # Llama a tu función de utils que busca en IGDB
        raw_results = search_igdb_games(query) 
        # Formateamos un poco para mandarlo por JSON
        for game in raw_results[:5]: # Mostramos 5 opciones
            results.append({
                'id': game['id'],
                'name': game['name'],
                'cover_url': game['cover']['url'] if 'cover' in game else ''
            })
    return JsonResponse({'results': results})

@login_required
def save_favorite(request):
    if request.method == 'POST':
        slot = int(request.POST.get('slot'))
        game_id = request.POST.get('game_id')
        game_name = request.POST.get('game_name')
        cover_url = request.POST.get('cover_url')

        FavoriteGame.objects.update_or_create(
            user=request.user,
            slot=slot,
            defaults={'game_id': game_id, 'game_name': game_name, 'cover_url': cover_url}
        )
        return JsonResponse({'status': 'success'})
    return JsonResponse({'status': 'error'})

@login_required
def remove_favorite(request, slot):
    if request.method == 'POST':
        FavoriteGame.objects.filter(user=request.user, slot=slot).delete()
        return JsonResponse({'status': 'success'})
    return JsonResponse({'status': 'error'})

@login_required
def upload_story(request):
    if request.method == 'POST' and request.FILES.get('image'):
        game_name = request.POST.get('game_name', '')
        Story.objects.create(
            user=request.user,
            image=request.FILES['image'],
            game_name=game_name
        )
        return JsonResponse({'status': 'success'})
    return JsonResponse({'status': 'error'})

def terms(request):
    return render(request, 'core/terms.html')

def privacy(request):
    return render(request, 'core/privacy.html')

@login_required
def toggle_follow(request, username):
    target_user = get_object_or_404(User, username=username)
    if target_user == request.user:
        return JsonResponse({'status': 'error', 'message': 'No puedes seguirte a ti mismo'})

    follow, created = Follow.objects.get_or_create(follower=request.user, followed=target_user)
    
    if not created:
        follow.delete()
        action = 'unfollowed'
    else:
        action = 'followed'
        
    return JsonResponse({
        'status': 'success', 
        'action': action,
        'count': target_user.followers.count()
    })

def follow_list(request, username, list_type):
    profile_user = get_object_or_404(User, username=username)
    if list_type == 'followers':
        users = [f.follower for f in profile_user.followers.all()]
        title = "Seguidores"
    else:
        users = [f.followed for f in profile_user.following.all()]
        title = "Siguiendo"
        
    return render(request, 'core/follow_list.html', {
        'profile_user': profile_user,
        'users_list': users,
        'title': title
    })

@login_required
def delete_review(request, post_id):
    # Solo el dueño puede borrar su reseña[cite: 2]
    post = get_object_or_404(ActivityPost, id=post_id, user=request.user)
    post.delete()
    return JsonResponse({'status': 'success'})

@login_required
def collection_view(request):
    # Traemos todos los juegos del usuario ordenados por fecha
    user_games = UserGame.objects.filter(user=request.user).order_by('-added_at')
    
    # Filtramos por estados
    playing = user_games.filter(status='playing')
    finished = user_games.filter(status='finished')
    completed = user_games.filter(status='completed')
    backlog = user_games.filter(status='backlog')
    abandoned = user_games.filter(status='abandoned')
    paused = user_games.filter(status='paused')
    
    # Calculamos el tiempo total jugado sumando horas y minutos
    total_h = sum(g.hours for g in user_games)
    total_m = sum(g.minutes for g in user_games)
    total_h += total_m // 60
    
    # Creamos el diccionario stats EXACTAMENTE como lo pide el HTML
    stats = {
        'total_games': user_games.count(),
        'playing': playing.count(),
        'finished': finished.count(),
        'completed': completed.count(),
        'backlog': backlog.count(),
        'abandoned': abandoned.count(),
        'paused': paused.count(),
        'playtime': f"{total_h}h",
    }
    
    context = {
        'user_games': user_games,
        'playing': playing,
        'finished': finished,
        'completed': completed,
        'backlog': backlog,
        'abandoned': abandoned,
        'stats': stats,
    }
    
    return render(request, 'core/collection.html', context)

def editar_perfil(request):
    if request.method == 'POST':
        perfil_usuario, created = UserProfile.objects.get_or_create(user=request.user)
        form = PerfilForm(request.POST, request.FILES, instance=perfil_usuario)
        
        if form.is_valid():
            form.save()
            return redirect('profile', request.user.username)