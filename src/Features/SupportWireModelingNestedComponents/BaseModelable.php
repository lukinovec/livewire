<?php

namespace Livewire\Features\SupportWireModelingNestedComponents;

use function Livewire\store;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BaseModelable extends LivewireAttribute
{
    public function mount($params, $parent)
    {
        if ($parent && (isset($params['wire:model.live']) || isset($params['wire:model']))) {
            if (isset($params['wire:model.live'])) {
                $outer = $params['wire:model.live'];
                store($this->component)->push('bindingsAreLive', true);
            } elseif (isset($params['wire:model'])) {
                $outer = $params['wire:model'];
                store($this->component)->push('bindingsAreLive', false);
            }
            $inner = $this->getName();

            store($this->component)->push('bindings', $inner, $outer);

            $this->setValue(data_get($parent, $outer));
        }
    }

    // This update hook is for the following scenario:
    // An modelable value has changed in the browser.
    // A network request is triggered from the parent.
    // The request contains both parent and child component updates.
    // The parent finishes it's request and the "updated" value is
    // overridden in the parent's lifecycle (ex. a form field being reset).
    // Without this hook, the child's value will not honor that change
    // and will instead still be updated to the old value, even though
    // the parent changed the bound value. This hook detects if the parent
    // has provided a value during this request and ensures that it is the
    // final value for the child's request...
    function update($fullPath, $newValue)
    {
        if (store($this->component)->get('hasBeenSeeded', false)) {
            $oldValue = $this->getValue();

            return function () use ($oldValue) {
                $this->setValue($oldValue);
            };
        }
    }
}
