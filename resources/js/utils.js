/**
* Sheaf Utility Functions
* Provides reactive magic property registration for Alpine.js
*/

export default function defineReactiveMagicProperty(name, rawObject, alpineInstance = null) {
    const alp = alpineInstance || window.Alpine;
    if (!alp) {
        console.warn('Alpine instance not available for defineReactiveMagicProperty:', name);
        return;
    }

    const instance = alp.reactive(rawObject);

    /** 
     * Reactive objects are plain proxies and do not support hooks like stores,
     * or scopes in Alpine.js so we initialize manually 
     */
    if (typeof instance.init === 'function') {
        instance.init();
    }

    alp.magic(name, () => instance);

    // Register the magic property globally
    window[name[0].toUpperCase() + name.slice(1)] = instance;
    return instance;
}