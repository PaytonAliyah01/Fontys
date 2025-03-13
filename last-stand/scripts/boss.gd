extends CharacterBody2D

@export var base_health: int = 1000  # Starting health
@export var speed: float = 185  # Movement speed
@export var damage: int = 20  # Amount of damage a boss deals

var health: int  # Current health
var target: Node2D  # Reference to the player
var direction: Vector2 = Vector2.ZERO  # Movement direction

@onready var damage_area = $Area2D

func _ready():
	add_to_group("enemies")  # Soldiers
	health = base_health
	
	if damage_area:
		damage_area.body_entered.connect(_on_body_entered)
	else:
		print("Error: Area2D not found!")

	# Find the player in the scene
	_find_player()

func _physics_process(_delta):
	# Ensure the player still exists before accessing its position
	if target and is_instance_valid(target):
		# Move towards the player
		direction = (target.global_position - global_position).normalized()
		velocity = direction * speed  # Set movement speed
		move_and_slide()  # Move while checking for collisions
	else:
		# If the player is no longer found, search for the player again
		_find_player()


# Function to find the player in the scene
func _find_player():
	# Get the first player in the scene (assuming they are in the 'player' group)
	var players = get_tree().get_nodes_in_group("player")
	if players.size() > 0:
		target = players[0]
	else:
		target = null 

# Function to handle collision and apply damage to the player
func _on_body_entered(body: Node) -> void:
	if body.is_in_group("player"):  # Check if the body is the player
		print("Boss hit the player! Dealing", damage, "damage.")
		body.take_damage(damage)  # Call the player's take_damage function

# Function for taking damage
func take_damage(amount: int):
	health -= amount  # Decrease health by damage amount
	health = max(health, 0)  # Prevent negative health
	print("Boss took", amount, "damage! Health left:", health)

	if health <= 0:
		die()

# Function for dying
func die():
	print("Boss Defeated!")
	queue_free()  # Remove the boss from the scene
