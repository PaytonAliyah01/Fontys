extends Node2D

@onready var home_button = $HomeButton 

func _ready():
	home_button.pressed.connect(_on_home_pressed)

func _on_home_pressed():
	# Go to the start screen (change the scene to the main menu or start screen)
	get_tree().change_scene_to_file("res://scenes/StartScreen.tscn")
