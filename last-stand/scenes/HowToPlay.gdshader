shader_type canvas_item;

uniform float darken_strength : hint_range(0.0, 1.0) = 0.3;  // Controls how dark the image gets

void fragment() {
    vec4 tex_color = texture(TEXTURE, UV); // Get the original texture color
    vec3 darkened_color = tex_color.rgb * (1.0 - darken_strength); // Reduce brightness
    COLOR = vec4(darkened_color, tex_color.a); // Apply the darkened color
}
