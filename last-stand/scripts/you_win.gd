extends Control

@onready var restart_button = $RestartButton
@onready var home_button = $HomeButton
@onready var background_image = $TextureRect


func _ready():
	# Connect the buttons to their respective functions
	restart_button.pressed.connect(_on_restart_pressed)
	home_button.pressed.connect(_on_home_pressed)

func _on_restart_pressed():
	# Reload the current scene to restart the game
	get_tree().reload_current_scene()

func _on_home_pressed():
	# Go to the start screen (change the scene to the main menu or start screen)
	get_tree().change_scene_to_file("res://scenes/StartScreen.tscn")
