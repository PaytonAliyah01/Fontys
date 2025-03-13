extends Control

@onready var back_button = $BackButton  # A button to go back to the main menu
@onready var background_image = $TextureRect

func _ready():
	back_button.pressed.connect(_on_back_button_pressed)

func _on_back_button_pressed():
	get_tree().change_scene_to_file("res://scenes/StartScreen.tscn")  # Go back to the main menu
