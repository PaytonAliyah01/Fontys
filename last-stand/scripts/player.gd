extends CharacterBody2D

@export var speed: float = 200
@export var dash_speed: float = 600
@export var dash_duration: float = 0.3
@export var dash_cooldown: float = 2.5
@onready var bullet_scene = preload("res://scenes/projectiles/Bullet.tscn")
@onready var health_bar = $HealthBar  # Reference to the HealthBar node

var dash_time = 0
var last_dash = -dash_cooldown
var movement_velocity: Vector2 = Vector2.ZERO  # Avoids shadowing CharacterBody2D's velocity

@export var max_health: int = 100
var health: int = max_health
var is_dead: bool = false  # Track if the player is dead

func _ready():
	# Set the initial value of the health bar
	health_bar.max_value = max_health
	health_bar.value = health

func _process(delta):
	move(delta)
	
	# Check if the player presses the shoot button
	if Input.is_action_just_pressed("shoot"):
		shoot()


func move(delta):
	# Prevent movement if the player is dead
	if is_dead:
		return
	
	var direction = Vector2.ZERO
	
	# Handling directional movement
	if Input.is_action_pressed("move_up"):
		direction.y -= 1
	if Input.is_action_pressed("move_down"):
		direction.y += 1
	if Input.is_action_pressed("move_left"):
		direction.x -= 1
	if Input.is_action_pressed("move_right"):
		direction.x += 1

	# Normalize the direction to ensure consistent speed
	if direction.length() > 0:
		direction = direction.normalized()

	# Handle dashing
	if can_dash():
		movement_velocity = direction * dash_speed
		dash_time = dash_duration
		last_dash = Time.get_ticks_msec() / 1000.0
	elif dash_time > 0:
		dash_time -= delta
		movement_velocity = direction * dash_speed
	else:
		movement_velocity = direction * speed

	# Set the velocity
	velocity = movement_velocity

	# Apply movement with move_and_slide (velocity is already set)
	move_and_slide()

func can_dash():
	return Input.is_action_just_pressed("dash") and (Time.get_ticks_msec() / 1000.0 - last_dash >= dash_cooldown)

# The shooting function
func shoot():
	var bullet = bullet_scene.instantiate()  # Create the bullet instance
	get_parent().add_child(bullet)  # Add the bullet to the scene

	bullet.position = position  # Spawn bullet at player's position
	
	# Get the direction towards the mouse cursor and normalize it
	var direction = (get_global_mouse_position() - position).normalized()
	
	bullet.direction = direction  # Pass the direction to the bullet
	bullet.rotation = direction.angle()  # Rotate the bullet to face the direction

# Function to apply damage to the player
func take_damage(amount: int):
	health -= amount
	print("Player health: " + str(health))

	# Update the health bar
	health_bar.value = health

	if health <= 0 and !is_dead:
		die()

# Function to handle player death
func die():
	is_dead = true  # Set the player as dead
	print("Player has died! Loading Game Over screen...")

	# Load the Game Over scene
	get_tree().change_scene_to_file("res://scenes/GameOver.tscn")
