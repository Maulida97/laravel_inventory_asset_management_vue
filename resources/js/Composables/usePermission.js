import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Composable for role and permission checking aligned with Spatie Laravel Permission.
 */
export function usePermission() {
    const page = usePage();

    const user = computed(() => page.props.auth?.user || null);

    const roles = computed(() => {
        return user.value?.roles || [];
    });

    const permissions = computed(() => {
        return user.value?.permissions || [];
    });

    const isSuperAdmin = computed(() => {
        return roles.value.includes('Super Admin');
    });

    /**
     * Check if user has a specific permission (Super Admin always has access).
     * @param {string} permissionName
     * @returns {boolean}
     */
    const can = (permissionName) => {
        if (isSuperAdmin.value) return true;
        return permissions.value.includes(permissionName);
    };

    /**
     * Check if user has ANY of the specified permissions.
     * @param {string[]} permissionNames
     * @returns {boolean}
     */
    const hasAnyPermission = (permissionNames = []) => {
        if (isSuperAdmin.value) return true;
        return permissionNames.some((p) => permissions.value.includes(p));
    };

    /**
     * Check if user has a specific role.
     * @param {string} roleName
     * @returns {boolean}
     */
    const hasRole = (roleName) => {
        return roles.value.includes(roleName);
    };

    /**
     * Check if user has ANY of the specified roles.
     * @param {string[]} roleNames
     * @returns {boolean}
     */
    const hasAnyRole = (roleNames = []) => {
        return roleNames.some((r) => roles.value.includes(r));
    };

    return {
        user,
        roles,
        permissions,
        isSuperAdmin,
        can,
        hasAnyPermission,
        hasRole,
        hasAnyRole,
    };
}
