<?php
/*
Plugin Name: RF score
Plugin URI:
Description: Show score on website
Version: 2.0
Author: Runar Flåten
Author URI: runar.xyz
License: GPLv2
*/

add_action( 'admin_enqueue_scripts', 'rfscore_enqueue_scripts' );

function rfscore_enqueue_scripts() {
  wp_register_script( 'rfscore-script', plugins_url( '/js/goalscorer.js', __FILE__), array('jquery'), true);

  wp_enqueue_script( 'rfscore-script' );
}

add_action( 'add_meta_boxes', 'rfscore_register_meta_box');

function rfscore_register_meta_box() {
  add_meta_box(
    'rf-score-meta',
    'Score',
    'rf_score_meta_box',
    'post',
    'side',
    'high'
  );

  add_meta_box(
    'rf-goals-meta',
    'Goals',
    'rf_goals_meta_box',
    'post',
    'side',
    'high'
  );

  add_meta_box(
    'rf-game-meta',
    'Is this a game',
    'rf_game_meta_box',
    'post',
    'side',
    'high'
  );
}

function rf_game_meta_box() {
  global $post;
  $checked = get_post_meta( $post->ID, 'is_game', true);
  echo '<label for="is_game">Click if this post is about a game</label><br>';
  if ( empty($checked)) {
    echo '<input type="checkbox" name="is_game"></input>';
  } else{
    echo '<input type="checkbox" name="is_game" checked="checked"></input>';
  }
}

function rf_score_meta_box() {
  global $post;
  $values = get_post_custom(
    $post->ID
  );
  $score_home = isset( $values['score_home']) ? $values['score_home'][0] : '';
  $score_away = isset( $values['score_away']) ? $values['score_away'][0] : '';
  $team_home = isset( $values['team_home']) ? $values['team_home'][0] : '';
  $team_away = isset( $values['team_away']) ? $values['team_away'][0] : '';
  wp_nonce_field( 'meta-box-save', 'rfscore-plugin' );
  ?>

  <label>Home team</label>
  <input type="text" name="team_home" placeholder="Home team" value="<?php echo $team_home ?>">
  <input type="text" name="score_home" placeholder="Score home team" value="<?php echo $score_home ?>">
  <br><label>Away team</label>
  <input type="text" name="team_away" placeholder="Away team" value="<?php echo $team_away ?>">
  <input type="text" name="score_away" placeholder="Score away team" value="<?php echo $score_away ?>">
  <?php
}

add_action( 'save_post', 'rf_score_save_data' );

function rf_score_save_data( $post_id) {
  if ( get_post_type( $post_id ) == 'post') {
    if ( defined( 'DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
      return;
    }
    if( !current_user_can( 'edit_post', $post_id ) ) return;

    wp_verify_nonce( 'meta-box-save', 'rfscore-plugin' );
    //write_log($_POST);
    if ( isset ($_POST['is_game'])) {
      update_post_meta( $post_id, 'is_game', True);
    }

    if ( isset ($_POST['score_home'])) {
      update_post_meta( $post_id, 'score_home', absint($_POST['score_home']));
    }
    if ( isset ($_POST['score_away'])) {
      update_post_meta( $post_id, 'score_away', absint($_POST['score_away']));
    }
    if ( isset ($_POST['team_home'])) {
      update_post_meta( $post_id, 'team_home', esc_attr($_POST['team_home']));
    }

    if ( isset ($_POST['team_away'])) {
      update_post_meta( $post_id, 'team_away', esc_attr($_POST['team_away']));
    }

    if ( isset( $_POST['num_goals'])) {
      update_post_meta( $post_id, 'num_goals', absint($_POST['num_goals']));
    }

    $i = 1;

    $goals = $_POST['goal'];
    write_log($goals);
    $goal_array = array();
    while( isset( $goals[$i])){
      $goal_data = $goals[$i];
      $goal_array[$i] = $goal_data;
      write_log($goal_data);
      update_post_meta( $post_id, '_goal[' . $i . ']', $goal_data);
      $i++;
    }
    update_post_meta( $post_id, '_goals', $goal_array);
  }
}



function rf_goals_meta_box() {
  global $post;

  wp_nonce_field( 'meta-box-save', 'rfscore-plugin');
  ?>
  <table id="goalscorerTable">
    <thead>
      <tr>
        <th>Num</th>
        <th>Min</th>
        <th>Name</th>
        <th>Home goal</th>
      </tr>
    </thead>
    <tbody>
    <?php
      $num_goals = get_post_meta( $post->ID, 'num_goals', true);
      echo '<tr><td><input id="numGoals" type=hidden name="num_goals" value="' . $num_goals . '"></input></td></tr>';

      $i = 1;
      $goals = get_post_meta( $post->ID, '_goals', true);
      while(isset($goals[$i]) && $num_goals >= $i) {
        $values = $goals[$i];
        $min = $values['min'];
        $name = $values['name'];
        $home = isset( $values['home']) ? $values['home'] : '';

        echo '<tr><td>' . $i . '</td>';
        echo '<td><input type="text" size="3" name="goal[' . $i . '][min]" value="' . $min . '"></input></td>';
        echo '<td><input type="text" size="10" name="goal[' . $i . '][name]" value="' . $name . '"></input></td>';
        if ($home) {
          echo '<td><input type="checkbox" name="goal[' . $i . '][home]" checked="checked"></input></td>';
        } else {
          echo '<td><input type="checkbox" name="goal[' . $i . '][home]"></input></td>';
        }
        echo '</tr>';
        $i++;
      }
     ?>
    </tbody>
</table>
<button id="goal_add">Add goal</button>
<button id="goal_delete">Delete goal</button>
  <?php
}

 ?>
