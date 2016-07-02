<modal :show.sync="showShortcuts">
	<template slot="header">Keyboard Shortcuts</template>
	<template slot="body">

		<div class="shortcut-pair">
			<span class="shortcut-key">
				<span class="shortcut">shift</span><span class="shortcut-joiner">+</span><span class="shortcut">?</span>
			</span>
			<span class="shortcut-value">Show Keyboard Shortcuts</span>
		</div>

		<div class="shortcut-pair">
			<span class="shortcut-key">
				<span class="shortcut">/</span> <span class="shortcut-joiner">or</span>
				<span class="shortcut">ctrl</span><span class="shortcut-joiner">+</span><span class="shortcut">f</span>
			</span>
			<span class="shortcut-value">Search</span>
		</div>

		<div class="shortcut-pair">
			<span class="shortcut-key">
				<span class="shortcut">cmd</span><span class="shortcut-joiner">+</span><span class="shortcut">s</span>
			</span>
			<span class="shortcut-value">Publish Content</span>
		</div>

		<div class="shortcut-pair">
			<span class="shortcut-key">
				<span class="shortcut">Esc</span>
			</span>
			<span class="shortcut-value">Close this Window</span>
		</div>

	</template>
</modal>
