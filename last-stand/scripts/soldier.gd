extends CharacterBody2D

@export var speed: float = 105  # Movement speed
@export var max_health: int = 100  # Max health for the soldier
@export var damage: int = 10  # Amount of damage a soldier deals

var health: int = max_health  # Current health of the soldier
var target: Node2D  # Reference to the player
var direction: Vector2 = Vector2.ZERO  # Movement direction

@onready var damage_area = $Area2D  # Reference to the Area2D for collision detection

func _ready():
	add_to_group("enemies")  # Soldiers
	# Connect the signal when the soldier collides with something
	damage_area.body_entered.connect(_on_body_entered)

	# Find the player in the scene
	_find_player()

func _physics_process(_delta):
	# Ensure the player still exists before accessing its position
	if target and is_instance_valid(target):
		direction = (target.global_position - global_position).normalized()
		velocity = direction * speed  # Set movement speed
		move_and_slide()  # Move while checking for collisions
	else:
		# If the target was deleted, search for a new one
		_find_player()

# Function to find the player in the scene
func _find_player():
	# Get the first player in the scene (assuming they are in the 'player' group)
	var players = get_tree().get_nodes_in_group("player")
	if players.size() > 0:
		target = players[0]  # Set the first player found as the target
	else:
		target = null  # No players found

# Function to handle collision and apply damage to the player
func _on_body_entered(body: Node) -> void:
	if body.is_in_group("player"):  # Check if the body is the player
		print("Soldier hit the player!")
		body.take_damage(damage)  # Call the player's take_damage function

# Function for taking damage
func take_damage(amount: int):
	health -= amount  # Decrease health by damage amount
	health = max(health, 0)  # Prevent negative health

	if health <= 0:
		die()

# Function for dying
func die():
	queue_free()  # Remove the soldier from the scene when they die
