extends Node2D

@export var spawn_points: Array = [
	Vector2(100, 200), 
	Vector2(200, 300), 
	Vector2(300, 400), 
	Vector2(400, 500), 
	Vector2(500, 600), 
	Vector2(600, 700),
	Vector2(700, 800),
	Vector2(800, 900),
	Vector2(900, 1000),
	Vector2(1000, 1100),
	Vector2(150, 250), 
	Vector2(250, 350), 
	Vector2(350, 450), 
	Vector2(450, 550), 
	Vector2(550, 650), 
	Vector2(650, 750),
	Vector2(750, 850),
	Vector2(850, 950),
	Vector2(950, 1050),
	Vector2(1050, 1150),
	Vector2(100, 300), 
	Vector2(200, 400), 
	Vector2(300, 500), 
	Vector2(400, 600), 
	Vector2(500, 700), 
	Vector2(600, 800),
	Vector2(700, 900),
	Vector2(800, 1000),
	Vector2(900, 1100),
	Vector2(1000, 1200),
]

@onready var timer = $SpawnTimer  
@onready var soldiers_left_label = $SoldiersLeftLabel  # Reference to the Label for soldiers left

var current_wave: int = 0
var active_soldiers: int = 0
var boss_active: bool = false  # Tracks if a boss is alive
var boss_scale_factor: float = 1.0  # Tracks how much bigger the boss should be each wave

# Fixed number of soldiers per wave
var wave_config = [25, 30, 35, 40, 45, 50]  
var max_waves = wave_config.size()  

func _ready():
	randomize()  
	if timer:
		if not timer.timeout.is_connected(_on_spawn_timer_timeout):  # Prevent duplicate connections
			timer.timeout.connect(_on_spawn_timer_timeout)
			timer.stop()
	else:
		print("Error: Timer node is not found!")

	timer.start(5)  # Start first wave in 5 seconds

func _spawn_wave():
	if current_wave >= max_waves:  
		return

	# Ensure no new wave spawns until all previous soldiers & bosses are defeated
	if active_soldiers > 0 or boss_active:
		print("Wave delayed: Still " + str(active_soldiers) + " soldiers or a boss is active.")
		return

	var soldier_count = wave_config[current_wave]  
	active_soldiers = soldier_count  

	print("Wave " + str(current_wave + 1) + ": Spawning " + str(soldier_count) + " soldiers.")

	for i in range(soldier_count):
		_spawn_soldier("left_to_right")

	current_wave += 1  # Move to next wave after spawning soldiers
	update_soldiers_left_label()  # Update the label for soldiers left

func _spawn_soldier(move_type):
	if spawn_points.is_empty():
		print("Error: No spawn points available.")
		return

	var soldier_scene = preload("res://scenes/Characters/Soldier.tscn")
	var soldier = soldier_scene.instantiate()

	# Pick a spawn point from the available spawn points
	var spawn_position = spawn_points[randi() % spawn_points.size()]
	spawn_position += Vector2(randf_range(-10, 10), randf_range(-10, 10))  # Add randomness to spread the spawn slightly

	# Ensure soldiers spawn within the visible area
	if !is_position_on_screen(spawn_position):
		print("Warning: Spawn position is off-screen. Trying again.")
		_spawn_soldier(move_type)  # Try spawning again if out of bounds
		return

	# Add soldier to the scene first
	self.add_child(soldier)
	soldier.global_position = spawn_position  # Set position after adding to the tree

	# Ensure soldiers do not spawn inside walls or other objects
	var max_attempts = 10
	var attempts = 0
	while soldier.test_move(soldier.transform, Vector2.ZERO) and attempts < max_attempts:
		spawn_position = spawn_points[randi() % spawn_points.size()] + Vector2(randf_range(-10, 10), randf_range(-10, 10))
		if is_position_on_screen(spawn_position):
			soldier.global_position = spawn_position
			attempts += 1

	if attempts >= max_attempts:
		print("Warning: Could not find a valid spawn position after multiple attempts.")

	if soldier.has_method("set_move_pattern"):
		soldier.set_move_pattern(move_type)

	soldier.z_index = 10
	soldier.tree_exited.connect(_on_soldier_died)  # Track soldier deaths

# Helper function to check if the position is within the visible screen
func is_position_on_screen(spawn_position: Vector2) -> bool:
	var screen_rect = Rect2(Vector2(0, 0), get_viewport().size)  # Get the screen's dimensions
	return screen_rect.has_point(spawn_position)  # Check if the position is within the screen bounds



# Function to update the label with the number of soldiers left
func update_soldiers_left_label():
	if soldiers_left_label:
		soldiers_left_label.text = "Soldiers Left: " + str(active_soldiers)

# Handle when a soldier dies
func _on_soldier_died():
	active_soldiers -= 1
	print("A soldier died. Active soldiers left: " + str(active_soldiers))

	# Update the label every time a soldier dies
	update_soldiers_left_label()

	# When all soldiers are dead, spawn the boss
	if active_soldiers <= 0 and not boss_active:
		print("All soldiers defeated. Spawning Boss.")
		_spawn_boss()

func _spawn_boss():
	print("A boss is appearing!")

	var boss_scene = preload("res://scenes/Characters/Boss.tscn")  # Load Boss scene
	var boss = boss_scene.instantiate()

	# Boss spawns at a fixed location
	var spawn_position = Vector2(400, 250)
	boss.position = spawn_position

	# Increase the boss size each time (scales up every wave)
	boss.scale *= boss_scale_factor  
	boss_scale_factor += 0.2  # Increase the size factor every wave

	boss_active = true  # Mark boss as active
	boss.tree_exited.connect(_on_boss_died)  # Track boss death

	self.add_child(boss)  # Add boss to scene

# Handle when the boss dies
func _on_boss_died():
	boss_active = false
	print("The boss has been defeated! Preparing next wave.")
	_start_next_wave()

func _start_next_wave():
	if current_wave < max_waves:
		print("Starting wave " + str(current_wave + 1) + " in 5 seconds.")
		timer.start(5)  
	else:
		# All waves completed, show "You Win" screen
		print("All waves are completed!")
		_show_win_screen()

func _show_win_screen():
	# Switch to the You Win screen
	get_tree().change_scene_to_file("res://scenes/YouWin.tscn")


func _on_spawn_timer_timeout():
	_spawn_wave()
