<x-mail::message>

# A recipe has been shared with you

{{ $sharerName }} shared their recipe "{{ $recipeName }}" with you. Click the button below to accept the shared recipe.

If you don't already have an account, you will be prompted to register before accepting the shared recipe. If you have an account but are not currently logged in, you will be prompted to login before accepting.

<x-mail::button :url="$url" color="primary">
    Accept Recipe
</x-mail::button>

</x-mail::message>