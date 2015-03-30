namespace :lorry do
	task :console, :command, :params, :role do |t, args|
		ask(:cmd, "cache:clear")
		command = args[:command] || fetch(:cmd)
		role = args[:role] || :all
		params = args[:params] || ''

		on release_roles(role) do
			within release_path do
				execute :php, fetch(:lorry_console_path), command, params
			end
		end
	end

	namespace :cache do
		task :clear do
		  invoke "lorry:console", "cache:clear"
		end

		task :warmup do
		  invoke "lorry:console", "cache:warmup"
		end
	end

    namespace :resque do
        task :restart do
            paths = fetch(:resque_workers);
            
            if paths
                on roles(:app) do
                    paths.each{ |path| execute :svc, "-h", path }
                end
            end
        end
    end
end
