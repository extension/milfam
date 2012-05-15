set :deploy_to, '/services/apache/vhosts/militaryfamilies.demo.extension.org/docroot/'
server 'militaryfamilies.demo.extension.org', :app, :web, :db, :primary => true

namespace :deploy do  
  # Link up various configs (valid after an update code invocation)
  task :link_and_copy_configs, :roles => :app do
    run <<-CMD
    rm -rf #{release_path}/wp-config.php &&
    ln -nfs /services/config/#{application}demo/wp-config.php #{release_path}/wp-config.php &&
    ln -nfs /services/wordpress/militaryfamilies.demo.extension.org/uploads #{release_path}/wp-content/uploads &&
    ln -nfs /services/config/#{application}demo/wordpress/robots.txt #{release_path}/robots.txt
    CMD
  end
end