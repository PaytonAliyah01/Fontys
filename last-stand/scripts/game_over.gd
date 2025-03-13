extends Control

@onready var start_button = $RestartButton
@onready var home_button = $HomeButton
@onready var background_image = $TextureRect

func _ready():
	# Connect buttons to their actions
	$RestartButton.pressed.connect(_on_restart_pressed)
	$HomeButton.pressed.connect(_on_home_pressed)
	
	# Remove all enemies from the scene when game over screen is shown
	_remove_all_enemies()

func _on_restart_pressed():
	get_tree().change_scene_to_file("res://scenes/main.tscn")  # Reloads the game scene

func _on_home_pressed():
	get_tree().change_scene_to_file("res://scenes/StartScreen.tscn")
# Function to remove all enemies when game over screen is shown
func _remove_all_enemies():
	# Get the main game scene, where enemies are
	var main_game_scene = get_tree().current_scene

	# Remove all enemies (soldiers and bosses)
	for enemy in main_game_scene.get_tree().get_nodes_in_group("enemy"):
		enemy.queue_free()
	print("All enemies have been removed from the scene.")
