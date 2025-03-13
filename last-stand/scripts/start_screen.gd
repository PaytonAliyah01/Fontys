extends Control

@onready var start_button = $StartButton
@onready var exit_button = $ExitButton
@onready var how_to_play_button = $HowToPlayButton
@onready var background_image = $TextureRect

func _ready():
	start_button.pressed.connect(_on_start_button_pressed)
	exit_button.pressed.connect(_on_exit_button_pressed)
	how_to_play_button.pressed.connect(_on_how_to_play_button_pressed)
	_remove_all_enemies()

func _on_start_button_pressed():
	get_tree().change_scene_to_file("res://scenes/main.tscn")  # Change to your game scene

func _on_exit_button_pressed():
	get_tree().quit()  # Exit the game

func _on_how_to_play_button_pressed():
	get_tree().change_scene_to_file("res://scenes/HowToPlay.tscn")  # Change to the How to Play scene

func _remove_all_enemies():
	# Get the main game scene, where enemies are
	var main_game_scene = get_tree().current_scene

	# Remove all enemies (soldiers and bosses)
	for enemy in main_game_scene.get_tree().get_nodes_in_group("enemy"):
		enemy.queue_free()
	print("All enemies have been removed from the scene.")
