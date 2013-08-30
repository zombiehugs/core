<div ng-app="shareDialog" id="ng-app" ng-controller="shareDialogController">
	<input type="text" ng-model="shareWith" placeholder="Share with"
		typeahead-on-select="share(shareWith)"
		typeahead="shareWith as shareWith.shareWithDisplayName for shareWith in
			searchForPotentialShareWiths($viewValue)"
		>
	<ul>
		<li ng-repeat="share in shares | orderBy:'-shareTime' | filter:shareListFilter">
			<span class="shareWith">{{share.shareWithDisplayName}}</span>
			<span class="permissions">
				<label>
					<input type="checkbox" ng-checked="isCreatable(share)"
						ng-click="toggleCreatable(share)">
					can edit
				</label>
				<span ng-show="isResharingAllowed()">
					<label>
						<input type="checkbox" ng-checked="isSharable(share)"
							ng-click="toggleSharable(share)">
						can share
					</label>
				</span>
			</span>
			<button class="svg action delete-icon delete-button" ng-click="unshare(share)"
				title="Unshare" />
		</li>
	</ul>
	<div id="link" ng-show="areLinksAllowed()">
		<label>
			<input type="checkbox" ng-model="link" ng-click="shareLink()">
			Share with link
		</label>
		<input type="text" ng-model="token" ng-show="link" />
		<div id="password" ng-show="link">
			<label>
				<input type="checkbox" ng-model="password">
				Password protect
			</label>
			<input type="password" placeholder="Password" ng-show="password" />
		</div>
	</div>
	<div id="expirationTime" ng-show="shares.length > 0">
		<label>
			<input type="checkbox" ng-model="expirationTime">
			Set expiration time
		</label>
		<div>
			<input datepicker1 type="text" placeholder="Date" ng-model="expirationDate" ng-show="expirationTime" />
		</div>
		<div>
			<input timepicker1 type="text" placeholder="Hour" ng-model="expirationHour"
				ng-show="expirationTime" />
		</div>
	</div>
</div>