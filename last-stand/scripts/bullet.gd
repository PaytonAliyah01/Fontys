extends Area2D

@export var speed: float = 700
@export var damage: int = 25
var direction: Vector2  

func _ready():
	# Ensure direction is set before moving the bullet
	if direction.length() > 0:
		rotate_towards_direction(direction)

	# Connect the signal to detect collisions with enemies
	connect("body_entered", Callable(self, "_on_Bullet_body_entered"))  # Corrected line

func _process(delta):
	# Move the bullet in the given direction
	position += direction * speed * delta

	# Check if bullet goes off-screen and remove it
	if !get_viewport_rect().has_point(position):
		queue_free()  # Removes the bullet when it leaves the screen

# This function rotates the bullet to face the direction it is moving
func rotate_towards_direction(target_direction: Vector2):
	if target_direction.length() > 0:
		rotation = target_direction.angle()  # Rotate the bullet to face the direction

# Detect collision with an enemy
func _on_Bullet_body_entered(body):
	if body.is_in_group("enemies"):  # Check if it's an enemy
		body.take_damage(damage)  # Call the enemy's take_damage function
		queue_free()  # Destroy the bullet after hitting
