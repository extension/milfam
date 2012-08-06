set :stages, %w(prod demo)
set :default_stage, "demo"
require 'capistrano/ext/multistage'

require 'capatross'
 
set :application, "milfam"
set :repository,  "git@github.com:extension/milfam.git"
set :branch, "master"
set :scm, "git"
set :user, "pacecar"
set :use_sudo, false
set :keep_releases, 3
ssh_options[:forward_agent] = true
set :port, 24
#ssh_options[:verbose] = :debug

after "deploy:update_code", "deploy:link_and_copy_configs"
after "deploy:update_code", "deploy:cleanup"


namespace :deploy do
  
  # Link up various configs (valid after an update code invocation)
  task :link_and_copy_configs, :roles => :app do
    
    if(stage.to_s == 'demo')
      sharedpath = "#{application}demo"
      uploads = "/services/wordpress/militaryfamilies.demo.extension.org/uploads"
    else
      sharedpath = "#{application}"
      uploads = "/services/wordpress/militaryfamilies.extension.org/uploads"
    end
      
    run <<-CMD
    rm -rf #{release_path}/wp-config.php &&
    ln -nfs /services/config/#{sharedpath}/wp-config.php #{release_path}/wp-config.php &&
    ln -nfs #{uploads} #{release_path}/wp-content/uploads &&
    ln -nfs /services/config/#{sharedpath}/robots.txt #{release_path}/robots.txt &&
    ln -nfs /services/config/#{sharedpath}/.htaccess #{release_path}/.htaccess
    CMD
  end
  
end



