namespace :doctrine do
	task :console, :command, :params, :role do |t, args|
		ask(:cmd, "cache:clear")
		command = args[:command] || fetch(:cmd)
		role = args[:role] || :all
		params = args[:params] || ''

		on release_roles(role) do
			within release_path do
				execute :php, fetch(:doctrine_console_path), command, params
			end
		end
	end

	namespace :orm do
		task :generate-proxies do
		  invoke "doctrine:console", "orm:generate-proxies"
		end
	end
end
