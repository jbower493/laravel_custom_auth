<x-mail::message>

# A recipe has been shared with you

{{ $sharerName }} shared their recipe "{{ $recipeName }}" with you. Click the button below to accept the shared recipe.

<x-mail::button :url="$url" color="primary">
    Accept Recipe
</x-mail::button>

</x-mail::message>