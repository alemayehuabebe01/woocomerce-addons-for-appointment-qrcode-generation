<?php 

if( !class_exists('Woo_Nehabi_Post_Type') ){
        class Woo_Nehabi_Post_Type{

            public function __construct(){
                
               // add_action('admin_menu', array( $this, 'add_menu'));
                add_action('init', array($this, 'create_post_type'));
        
            }

            public function create_post_type(){
                register_post_type(
                    'woo_nehabi',  
                    array(
                        'label' => __('Woo Nehabi', 'woo-nehabi'),   
                        'description' => __('Woo Nehabi', 'woo-nehabi'),  
                        'labels' => array(
                            'name' => __('Woo Nehabi', 'woo-nehabi'),  
                            'singular_name' => __('Property', 'woo-nehabi'),  
                            'add_new' => __('Create New Property', 'woo-nehabi'),
                            'add_new_item' => __('Add New Property', 'woo-nehabi'),
                            'view_item' => __('View Property', 'woo-nehabi'),
                            'view_items' => __('View Properties', 'woo-nehabi'),
                            'featured_image' => __('Property Image', 'woo-nehabi'),
                            'set_featured_image' => __('Set Property Image', 'woo-nehabi'),
                            'remove_featured_image' => __('Remove Property Image', 'woo-nehabi'),
                            'use_featured_image' => __('Use as Property Image', 'woo-nehabi'),
                            'insert_into_item' => __('Insert into Property', 'woo-nehabi'),
                            'uploaded_to_this_item' => __('Uploaded to this Property', 'woo-nehabi'),
                            'items_list' => __('Property List', 'woo-nehabi'),
                            'items_list_navigation' => __('Property List Navigation', 'woo-nehabi'),
                            'filter_items_list' => __('Filter Property List', 'woo-nehabi'),
                            'archives' => __('Property Archives', 'woo-nehabi'),
                            'attributes' => __('Property Attributes', 'woo-nehabi'),
                            'parent_item_colon' => __('Parent Property:', 'woo-nehabi'),
                            'all_items' => __('All Properties', 'woo-nehabi'),   
                            'new_item' => __('New Property', 'woo-nehabi'),
                            'edit_item' => __('Edit Property', 'woo-nehabi'),
                            'update_item' => __('Update Property', 'woo-nehabi'),
                            'search_items' => __('Search Property', 'woo-nehabi'),
                            'not_found' => __('Not found', 'woo-nehabi'),
                            'not_found_in_trash' => __('Not found in Trash', 'woo-nehabi'),
                        ),
                        'public' => true,
                        'supports' => array('title', 'editor', 'thumbnail', 'gallery'),
                        'hierarchical' => false,
                        'show_ui' => false,
                        'show_in_menu' => false,
                        'menu_position' => 5,
                        'show_in_admin_bar' =>false,
                        'show_in_nav_menus' => true,
                        'can_export' => true,
                        'has_archive' => true,
                        'exclude_from_search' => false,
                        'publicly_queryable' => true,
                        'show_in_rest' => false,
                        'menu_icon' => 'dashicons-admin-home',
                    )
                );
            }
                
            
        }

       
        }


