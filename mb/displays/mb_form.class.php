<?php

class MONGOBASE_FORM extends MONGOBASE_MODULE {

	public $FORM = null;
	private $got_form = false;
	private $construct_set = false;
	private $form_options = null;

	function __construct($name=null,$app=null,$args=null){
		parent::__construct($name,$app);
		$this->form_options = $args;
		$this->got_form = $this->form();
	}

	private function field_settings($field_settings = false, $form_settings = false){
		$default_field = array(
			'type'          => 'textbox',
			'position'		=> false,
			'id'            => false,
			'name'          => false,
			'current_value' => false,
			'default_value' => false,
			'placeholder'   => false,
			'required'      => false,
			'label'         => false,
			'class'         => 'blanked'
		);
		if(is_array($field_settings)){
			$settings = array_merge($default_field,$field_settings);
		}else{
			$settings = $default_field;
		}
		/* MODIFY THE SETTINGS ARRAY */
		if(isset($settings['default_value'])){
			if(isset($settings['current_value'])){
				if((empty($settings['current_value']))&&(!empty($settings['default_value']))){
					$settings['value'] = $settings['default_value'];
				}else{
					if(!empty($settings['current_value'])){
						$settings['value'] = $settings['current_value'];
					}else{
						$settings['value'] = false;
					}
				}
			}else{
				if(isset($settings['default_value'])){
					$settings['value'] = $settings['default_value'];
				}else{
					$settings['value'] = false;
				}
			}
		}elseif(isset($settings['current_value'])){
			$settings['value'] = $settings['current_value'];
		}else{
			$settings['value'] = false;
		} $this_wrap = false;
		if(isset($form_settings)){
			if(isset($settings['wrapped'])){
				if($settings['wrapped']!=true){
					$default_wrapped = $form_settings['styles']['wrapped'];
					if(isset($settings['wrapped'])){
						$this_wrap = $settings['wrapped'];
					}else{
						$this_wrap = $default_wrapped;
					}
				}
			}else{
				if(isset($form_settings['styles']['wrapped'])){
					if($form_settings['styles']['wrapped']===true){
						$this_wrap = true;
					}
				}
			}
		}else{
			if($settings['wrapped']===true){
				$this_wrap = true;
			}
		} $settings['this_wrap'] = $this_wrap;
		return $settings;
	}

	private function form_settings($args=false){
		$default_styles_wrapped = true;
		$default_styles_fluid = true;
		$default_styles_bg = '#F5F5F5';
		$default_styles_border = '1px solid #DDDDDD';
		$default_styles_color = '#757575';
		$default_styles_label = '#006699';
		$default_styles_hover_bg = '#EFEFEF';
		$default_styles_hover_border = '1px solid #CCCCCC';
		$default_styles_hover_color = '#333333';
		if(isset($args['styles']['wrapped'])){
			$this_styles_wrapped = $args['styles']['wrapped'];
		} if(isset($this_styles_wrapped)){
			$styles_wrapped = $this_styles_wrapped;
		}else{
			$styles_wrapped = $default_styles_wrapped;
		} if(isset($args['styles']['fluid'])){
			$this_styles_fluid = $args['styles']['fluid'];
		} if(isset($this_styles_fluid)){
			$styles_fluid = $this_styles_fluid;
		}else{
			$styles_fluid = $default_styles_fluid;
		} if(isset($args['styles']['bg'])){
			$this_styles_bg = $args['styles']['bg'];
		} if(isset($this_styles_bg)){
			$styles_bg = $this_styles_bg;
		}else{
			$styles_bg = $default_styles_bg;
		} if(isset($args['styles']['border'])){
			$this_styles_border = $args['styles']['border'];
		} if(isset($this_styles_border)){
			$styles_border = $this_styles_border;
		}else{
			$styles_border = $default_styles_border;
		} if(isset($args['styles']['color'])){
			$this_styles_color = $args['styles']['color'];
		} if(isset($this_styles_color)){
			$styles_color = $this_styles_color;
		}else{
			$styles_color = $default_styles_color;
		} if(isset($args['styles']['label'])){
			$this_styles_label = $args['styles']['label'];
		} if(isset($this_styles_label)){
			$styles_label = $this_styles_label;
		}else{
			$styles_label = $default_styles_label;
		} if(isset($args['styles']['hover']['bg'])){
			$this_styles_hover_bg = $args['styles']['hover']['bg'];
		} if(isset($this_styles_hover_bg)){
			$styles_hover_bg = $this_styles_hover_bg;
		}else{
			$styles_hover_bg = $default_styles_hover_bg;
		} if(isset($args['styles']['hover']['border'])){
			$this_styles_hover_border = $args['styles']['hover']['border'];
		} if(isset($this_styles_hover_border)){
			$styles_hover_border = $this_styles_hover_border;
		}else{
			$styles_hover_border = $default_styles_hover_border;
		} if(isset($args['styles']['hover']['color'])){
			$this_styles_hover_color = $args['styles']['hover']['color'];
		} if(isset($this_styles_hover_color)){
			$styles_hover_color = $this_styles_hover_color;
		}else{
			$styles_hover_color = $default_styles_hover_color;
		}
		$default_options = array(
			'submit'        => true,
			'submit_text'   => $this->__('Submit'),
			'id'            => false,
			'class'         => 'mb-form',
			'method'        => 'post',
			'action'		=> false,
			'enctype'       => 'multipart/form-data',
			'debug'         => false,
			'fields'        => false,
			'styles'        => array(
				'wrapped'       => $styles_wrapped,
				'fluid'         => $styles_fluid,
				'bg'            => $styles_bg,
				'border'        => $styles_border,
				'color'         => $styles_color,
				'label'         => $styles_label,
				'hover'         => array(
					'bg'            => $styles_hover_bg,
					'border'        => $styles_hover_border,
					'color'         => $styles_hover_color
				)
			)
		);
		if(is_array($args)){
			$settings = array_merge($default_options,$args);
		}else{
			$settings = $default_options;
		} /* MODIFY SETTINGS ARRAY */
		if(isset($settings['styles']['wrapped'])){ }else{ $settings['styles']['wrapped'] = $styles_wrapped; }
		if(isset($settings['styles']['fluid'])){ }else{ $settings['styles']['fluid'] = $styles_fluid; }
		if(isset($settings['styles']['bg'])){ }else{ $settings['styles']['bg'] = $styles_bg; }
		if(isset($settings['styles']['border'])){ }else{ $settings['styles']['border'] = $styles_border; }
		if(isset($settings['styles']['color'])){ }else{ $settings['styles']['color'] = $styles_color; }
		if(isset($settings['styles']['label'])){ }else{ $settings['styles']['label'] = $styles_label; }
		if(isset($settings['styles']['hover']['bg'])){ }else{ $settings['styles']['hover'] = $styles_hover_bg; }
		if(isset($settings['styles']['hover']['border'])){ }else{ $settings['styles']['hover'] = $styles_hover_border; }
		if(isset($settings['styles']['hover']['color'])){ }else{ $settings['styles']['hover'] = $styles_hover_color; }
		return $settings;
	}

	private function form_styles(){
		$settings = $this->form_options;
		ob_start();
		?>
		<style>
		/* TODO: THIS NEEDS TO BE ADDED TO DYNAMIC PHP-BASED CSS STYLESHEET */

		form.<?php echo $settings['class']; ?> div.<?php echo $settings['class']; ?>-field-wrapper {
			vertical-align: top;
			display: inline-block;
			width: 100%;
		}

		form.<?php echo $settings['class']; ?> div.<?php echo $settings['class']; ?>-field-wrapper.half {
			display: inline-block;
			width: 48%;
		}

		form.<?php echo $settings['class']; ?> div.<?php echo $settings['class']; ?>-field-wrapper.third {
			display: inline-block;
			width: 31%;
		}

		form.<?php echo $settings['class']; ?> div.<?php echo $settings['class']; ?>-field-wrapper.thirds {
			display: inline-block;
			width: 65%;
		}

		form.<?php echo $settings['class']; ?> div.<?php echo $settings['class']; ?>-field-wrapper.left {
			padding-right: 2%;
		}

		form.<?php echo $settings['class']; ?> div.<?php echo $settings['class']; ?>-field-wrapper.middle {
			padding-left: 1%;
			padding-right: 1%;
			width: 32%;
		}

		form.<?php echo $settings['class']; ?> div.<?php echo $settings['class']; ?>-field-wrapper.right {
			padding-left: 2%;
		}

		form.<?php echo $settings['class']; ?> div.<?php echo $settings['class']; ?>-field-wrapper.thirds.right {
			padding-left: 1%;
			width: 66%;
		}
		form.<?php echo $settings['class']; ?> div.<?php echo $settings['class']; ?>-field-wrapper.thirds.left {
			padding-right: 1%;
			width: 66%;
		}

		form#<?php echo $settings['id']; ?> .input-wrapper,
		form.<?php echo $settings['class']; ?> .input-wrapper {
			display: block;
			padding: 8px;
			margin: 0 0 25px;
		}
		form#<?php echo $settings['id']; ?> .input-wrapper select,
		form.<?php echo $settings['class']; ?> .input-wrapper select {
			margin: 0 0 -1px !important;
		}
		form#<?php echo $settings['id']; ?> .input-wrapper .blanked,
		form.<?php echo $settings['class']; ?> .input-wrapper .blanked {
			background: transparent;
			border: none;
			border-color: transparent;
			display: block;
			width: 100%;
			padding: 0;
			margin: 0;
		}
		form#<?php echo $settings['id']; ?> label.<?php echo $settings['class']; ?>-label,
		form.<?php echo $settings['class']; ?> label.<?php echo $settings['class']; ?>-label {
			display: block;
			padding: 5px;
			margin: 0 0 5px;
			font-weight: bold;
			white-space: nowrap;
			overflow: hidden;
			cursor: pointer;
			color: <?php echo $settings['styles']['label']; ?>;
		}
		form#<?php echo $settings['id']; ?> textarea,
		form.<?php echo $settings['class']; ?> texarea {
			min-height: 85px;
		}

		form#<?php echo $settings['id']; ?> .<?php echo $settings['class']; ?>-submit,
		form.<?php echo $settings['class']; ?> .<?php echo $settings['class']; ?>-submit {
			clear: both;
			display: block;
			margin: 15px 0 25px;
		}

		/* FOR WRAPPED FIELDS ONLY */
		form#<?php echo $settings['id']; ?> .input-wrapper,
		form.<?php echo $settings['class']; ?> .input-wrapper {
			background-color: <?php echo $settings['styles']['bg']; ?>;
			border: <?php echo $settings['styles']['border']; ?>;
			color: <?php echo $settings['styles']['color']; ?>;
		}
		form#<?php echo $settings['id']; ?> .input-wrapper .blanked,
		form.<?php echo $settings['class']; ?> .input-wrapper .blanked {
			color: <?php echo $settings['styles']['color']; ?>;
		}

		/* FOR UN-WRAPPED FIELDS ONLY */
		form#<?php echo $settings['id']; ?> .not-wrapped,
		form.<?php echo $settings['class']; ?> .not-wrapped {
			background-color: <?php echo $settings['styles']['bg']; ?>;
			border: <?php echo $settings['styles']['border']; ?>;
			color: <?php echo $settings['styles']['color']; ?>;
		}

		/* RADIO BOXES */
		form#<?php echo $settings['id']; ?> .input-wrapper.radio,
		form.<?php echo $settings['class']; ?> .input-wrapper.radio {
			text-align: left;
			background: transparent;
			border-color: transparent;
			padding: 8px 2px
		}
		form#<?php echo $settings['id']; ?> .input-wrapper .radio-box,
		form.<?php echo $settings['class']; ?> .input-wrapper .radio-box {
			clear: both;
			float: left;
			display: inline-block;
			width: 5%;
			text-align: left;
			min-height:20px;
			vertical-align: top;
		}
		form#<?php echo $settings['id']; ?> .radio-label,
		form.<?php echo $settings['class']; ?> .radio-label {
			display: inline-block;
			float: left;
			width: auto;
			padding: 3px 0 0 4%;
			text-align: left;
			min-height:20px;
			vertical-align: sub;
		}

		/* CHECK BOXES */
		form#<?php echo $settings['id']; ?> .input-wrapper.checkbox,
		form.<?php echo $settings['class']; ?> .input-wrapper.checkbox {
			text-align: left;
			background: transparent;
			border-color: transparent;
			padding: 8px 2px;
		}
		form#<?php echo $settings['id']; ?> .input-wrapper .check-box,
		form.<?php echo $settings['class']; ?> .input-wrapper .check-box {
			display: inline-block;
			clear: both;
			float: left;
			width: 5%;
			text-align: left;
			min-height:20px;
			vertical-align: top;
		}
		form#<?php echo $settings['id']; ?> .check-label,
		form.<?php echo $settings['class']; ?> .check-label {
			display: inline-block;
			float: left;
			width: auto;
			padding: 3px 0 0 4%;
			text-align: left;
			min-height:20px;
			vertical-align: sub;
		}

		/* FINAL RE-RESETS */
		form.<?php echo $settings['class']; ?> label.<?php echo $settings['class']; ?>-label.checkbox,
		form.<?php echo $settings['class']; ?> label.<?php echo $settings['class']; ?>-label.radio {
			cursor: inherit;
		}

		/* BUTTONS */
		form#<?php echo $settings['id']; ?> input.<?php echo $settings['class']; ?>-submit,
		form.<?php echo $settings['class']; ?> input.<?php echo $settings['class']; ?>-submit {
			cursor: pointer;
		}

		</style>
		<?php
		$form_styles = ob_get_clean();
		return $form_styles;
	}

	private function textarea($id = false, $class = false, $name = false, $placeholder = false, $required = false, $value = false){
		$textarea = '<textarea id="'.$id.'" class="'.$class.'" name="'.$name.'" placeholder="'.$placeholder.'" autocomplete="off" '.$required.'>'.$value.'</textarea>';
		return $textarea;
	}

	private function select($id = false, $class = false, $name = false, $placeholder = false, $required = false, $value = false, $values = false){
		$selectbox = '<select id="'.$id.'" class="'.$class.'" name="'.$name.'" placeholder="'.$placeholder.'" autocomplete="off" '.$required.' >';
			if((is_array($values))&&(!empty($values))){
				foreach($values as $option_value => $option_label){
					if($option_value==$value){
						$selected = 'selected="selected"';
					}else{
						$selected = false;
					}
					$selectbox.= '<option value="'.$option_value.'" '.$selected.'>'.$option_label.'</option>';
				}
			}
		$selectbox.= '</select>';
		return $selectbox;
	}

	private function radio($id = false, $class = false, $name = false, $placeholder = false, $required = false, $value = false, $values = false){
		if((is_array($values))&&(!empty($values))){
			$radio = '';
			foreach($values as $option_value => $option_label){
				if($option_value==$value){
					$selected = 'checked="checked"';
				}else{
					$selected = false;
				}
				$radio.= '<input type="radio" group="'.$id.'" class="'.$class.' radio-box" name="'.$name.'" value="'.$option_value.'" '.$selected.'><span class="radio-label">'.$option_label.'</span>';
			}
		}
		return $radio;
	}

	private function checkbox($id = false, $class = false, $name = false, $placeholder = false, $required = false, $value = false, $values = false){
		if((is_array($values))&&(!empty($values))){
			$checkbox = '';
			foreach($values as $option_value => $option_label){
				if($option_value==$value){
					$selected = 'checked="checked"';
				}else{
					$selected = false;
				}
				$checkbox.= '<input type="checkbox" group="'.$id.'" class="'.$class.' check-box" name="'.$name.'" value="'.$option_value.'" '.$selected.'><span class="check-label">'.$option_label.'</span>';
			}
		}
		return $checkbox;
	}

	private function textbox($id = false, $class = false, $name = false, $placeholder = false, $required = false, $value = false, $type = false){
		$textbox = '<input type="'.$type.'" id="'.$id.'" class="'.$class.'" name="'.$name.'" value="'.$value.'" placeholder="'.$placeholder.'" autocomplete="off" '.$required.' />';
		return $textbox;
	}

	private function get_field($settings=false,$class=false,$required=false){
		$this_wrap = $settings['this_wrap'];
		if($this_wrap) $wrap = false;
		else $wrap = 'not-wrapped';
		if($settings['type']=='textarea'){
            return $this->textarea($settings['id'], $settings['class'].' '.$wrap.' '.$class.'-'.$settings['type'], $settings['name'], $settings['placeholder'], $required, $settings['value']);
        }elseif($settings['type']=='email'){
            return $this->textbox($settings['id'], $settings['class'].' '.$wrap.' '.$class.'-'.$settings['type'], $settings['name'], $settings['placeholder'], $required, $settings['value'], 'email');
        }elseif($settings['type']=='select'){
            return $this->select($settings['id'], $settings['class'].' '.$wrap.' '.$class.'-'.$settings['type'], $settings['name'], $settings['placeholder'], $required, $settings['value'], $settings['values']);
        }elseif($settings['type']=='radio'){
            return $this->radio($settings['id'], $settings['class'].' '.$wrap.' '.$class.'-'.$settings['type'], $settings['name'], $settings['placeholder'], $required, $settings['value'], $settings['values']);
        }elseif($settings['type']=='checkbox'){
            return $this->checkbox($settings['id'], $settings['class'].' '.$wrap.' '.$class.'-'.$settings['type'], $settings['name'], $settings['placeholder'], $required, $settings['value'], $settings['values']);
        }else{
            return $this->textbox($settings['id'], $settings['class'].' '.$wrap.' '.$class.'-'.$settings['type'], $settings['name'], $settings['placeholder'], $required, $settings['value'], false);
        }
	}

	private function field($field_settings=false,$form_settings=null){
		$settings = $this->field_settings($field_settings, $form_settings);
		if($form_settings===null) $form_settings = $this->form_settings($form_settings);
		$form_class = $form_settings['class'];
		$this_wrap = $settings['this_wrap'];
		if($settings['required']){ $required = 'required="required"'; }else{ $required = false; }
		if($this_wrap){ $settings['class'] = $settings['class'].' blanked'; }
		/* ADD FIELD WRAPPER */
		$field = '<div id="'.$settings['id'].'-wrapper" class="'.$form_class.'-field-wrapper '.$form_class.'-'.$settings['type'].'-wrapper '.$settings['position'].'">';
			/* ADD THE LABEL */
			if($settings['label']){
				$field.= '<label for="'.$settings['id'].'" class="'.$form_class.'-label '.$settings['type'].'">'.$settings['label'].'</label>';
			} if($this_wrap){
				$field.= '<span class="input-wrapper '.$settings['type'].'">';
			} $field.= $this->get_field($settings,$form_class,$required);
			if($this_wrap){
				$field.= '</span>';
			}
			$field.= '';
		$field.= '</div>';
		return $field;
	}

	public function form($force_refresh = false){

		if(!$force_refresh && $this->FORM!==null) return true;
		
		$settings = $this->form_settings($this->form_options);
		$form = '<form id="'.$settings['id'].'-form" class="'.$settings['class'].'" method="'.$settings['method'].'" action="'.$settings['action'].'" enctype="'.$settings['enctype'].'">';
			if($this->is_set($settings['fields'])){
				foreach($settings['fields'] as $key => $field){
					if(empty($field['id'])) $field['id'] = $key;
					if(empty($field['name'])) $field['name'] = $key;
					$form.= $this->field($field, $settings);
				}
			}
			$form.= '<input id="'.$settings['id'].'-submit" class="'.$settings['class'].'-submit" type="submit" value="'.$settings['submit_text'].'" />';
		$form.= '</form>';
		
		$this->FORM = $form;
		return true;
	}

	public function display(){
		print_r($this->form_styles());
		print_r($this->FORM);
	}

	public function get(){
		ob_start();
		$this->display();
		$form = ob_get_clean();
		return $form;
	}

	public function options(){

		if (isset($this->options) && ! empty($this->options)) return $this->options;

		$this->do_action('form_options',$this);

		return $this->options;

	}

}
