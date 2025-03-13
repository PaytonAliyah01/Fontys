shader_type canvas_item;

uniform float brightness : hint_range(1.0, 2.0) = 1.3; // Controls brightness level (1.0 = no change)

void fragment() {
    vec4 tex_color = texture(TEXTURE, UV); // Get original texture color
    vec3 brightened_color = tex_color.rgb * brightness; // Increase brightness
    COLOR = vec4(brightened_color, tex_color.a); // Apply brightness
}
